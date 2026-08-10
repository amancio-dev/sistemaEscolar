<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelClass;

    protected string $primaryKey;

    /** @var array<int, string> */
    protected array $searchable = [];

    protected string $singularLabel;

    protected string $pluralLabel;

    protected string $resource;

    /** @var array<int, string> */
    protected array $with = [];

    /** @var array<string, array<int, string>> */
    protected array $relationSearches = [];

    /** @var class-string<JsonResource>|null */
    protected ?string $apiResourceClass = null;

    /** @return array<string, mixed> */
    abstract protected function rules(?int $id = null): array;

    public function index(Request $request): JsonResponse|View
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 100);
        $search = trim((string) $request->query('busca', ''));
        $modelClass = $this->modelClass;

        $query = $modelClass::query()->with($this->with);
        $this->applyIndexScope($query, $request);

        if ($search !== '' && ($this->searchable !== [] || $this->relationSearches !== [])) {
            $query->where(function ($query) use ($search): void {
                foreach ($this->searchable as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', "%{$search}%");
                }

                foreach ($this->relationSearches as $relation => $columns) {
                    $query->orWhereHas($relation, function ($relationQuery) use ($columns, $search): void {
                        $relationQuery->where(function ($columnQuery) use ($columns, $search): void {
                            foreach ($columns as $index => $column) {
                                $method = $index === 0 ? 'where' : 'orWhere';
                                $columnQuery->{$method}($column, 'like', "%{$search}%");
                            }
                        });
                    });
                }
            });
        }

        $records = $query
            ->orderByDesc($this->primaryKey)
            ->paginate($perPage)
            ->withQueryString();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->pluralLabel} listados com sucesso.",
                'data' => $this->apiPage($records, $request),
            ]);
        }

        return view("{$this->resource}.index", [
            'records' => $records,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view("{$this->resource}.create", $this->formData());
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());
        $modelClass = $this->modelClass;

        $record = DB::transaction(function () use ($validated, $modelClass): Model {
            return $modelClass::create($this->prepareCreate($validated));
        });

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->singularLabel} cadastrado com sucesso.",
                'data' => $this->apiRecord($record, $request),
            ], 201);
        }

        return redirect()
            ->route("{$this->resource}.index")
            ->with('success', ucfirst($this->singularLabel).' cadastrado com sucesso.');
    }

    public function show(Request $request, int $id): JsonResponse|View
    {
        $modelClass = $this->modelClass;
        $record = $modelClass::query()->findOrFail($id);
        $this->authorizeRecord($request, $record);

        return response()->json([
            'message' => "{$this->singularLabel} consultado com sucesso.",
            'data' => $this->apiRecord($record, $request),
        ]);
    }

    public function edit(Request $request, int $id): View
    {
        $modelClass = $this->modelClass;
        $record = $modelClass::query()->findOrFail($id);
        $this->authorizeRecord($request, $record);

        return view("{$this->resource}.edit", [
            'record' => $record,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $modelClass = $this->modelClass;
        $record = $modelClass::query()->findOrFail($id);
        $this->authorizeRecord($request, $record);
        $validated = $request->validate($this->rules($id), $this->messages(), $this->attributes());

        DB::transaction(function () use ($record, $validated): void {
            $record->update($this->prepareUpdate($validated, $record));
            $this->afterUpdate($record);
        });

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->singularLabel} atualizado com sucesso.",
                'data' => $this->apiRecord($record->refresh(), $request),
            ]);
        }

        return redirect()
            ->route("{$this->resource}.index")
            ->with('success', ucfirst($this->singularLabel).' atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $modelClass = $this->modelClass;
        $record = $modelClass::query()->findOrFail($id);
        $this->authorizeRecord($request, $record);

        try {
            DB::transaction(function () use ($record): void {
                $record->delete();
                $this->afterDelete($record);
            });
        } catch (QueryException) {
            $message = "Não foi possível excluir {$this->singularLabel} porque há registros vinculados.";

            if ($this->isApiRequest($request)) {
                return response()->json(['message' => $message], 409);
            }

            return redirect()->back()->with('error', $message);
        }

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => "{$this->singularLabel} excluído com sucesso.",
            ]);
        }

        return redirect()
            ->route("{$this->resource}.index")
            ->with('success', ucfirst($this->singularLabel).' excluído com sucesso.');
    }

    /** @return array<string, mixed> */
    protected function formData(): array
    {
        return [];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [];
    }

    /** @return array<string, string> */
    protected function attributes(): array
    {
        return [];
    }

    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*');
    }

    protected function apiPage(LengthAwarePaginator $records, Request $request): LengthAwarePaginator
    {
        if ($this->apiResourceClass === null) {
            return $records;
        }

        $resourceClass = $this->apiResourceClass;
        $records->setCollection($records->getCollection()->map(
            fn (Model $record): array => (new $resourceClass($record))->resolve($request),
        ));

        return $records;
    }

    /** @return Model|array<string, mixed> */
    protected function apiRecord(Model $record, Request $request): Model|array
    {
        if ($this->apiResourceClass === null) {
            return $record;
        }

        $record->loadMissing($this->with);
        $resourceClass = $this->apiResourceClass;

        return (new $resourceClass($record))->resolve($request);
    }

    /**
     * Permite que um recurso limite a listagem ao contexto do usuário autenticado.
     */
    protected function applyIndexScope(Builder $query, Request $request): void
    {
        // Sem escopo adicional por padrão.
    }

    /** @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    protected function prepareCreate(array $validated): array
    {
        return $validated;
    }

    /** @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    protected function prepareUpdate(array $validated, Model $record): array
    {
        return $validated;
    }

    protected function afterUpdate(Model $record): void
    {
        // Hook para recursos que precisam sincronizar dados relacionados.
    }

    protected function afterDelete(Model $record): void
    {
        // Hook para recursos que precisam remover dados relacionados.
    }

    protected function authorizeRecord(Request $request, Model $record): void
    {
        // Recursos sem regra contextual já são protegidos pelo middleware de rota.
    }
}
