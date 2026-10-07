<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageProcessor;
use App\Support\Audit;
use App\Support\RichText;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * One consistent list / create / edit / delete experience for every piece of website content.
 * A concrete controller only describes its model, columns and form fields in config().
 *
 * config() keys:
 *   model, title (plural), singular, route (route-name prefix, e.g. "admin.properties"),
 *   search (columns), filters (name/label/options), order (column, direction), per_page,
 *   columns [ ['label','key' | 'value' => fn($m), 'type' => text|money|badge|image|date|bool|limit, 'main' => bool] ],
 *   fields  [ ['name','label','type' => text|textarea|number|select|toggle|image|images|datetime|lines,
 *              'rules','options','help','full','folder','section','placeholder','max','list'] ],
 *   view    (optional fn($m) => public URL for a "view on site" button)
 */
abstract class ResourceController extends Controller
{
    abstract protected function config(): array;

    /** Extra query constraints / eager loading for the list. */
    protected function scope(Builder $query, Request $request): void {}

    /** Adjust validated data before saving (slugs, ownership…). */
    protected function prepare(array $data, ?Model $model, Request $request): array
    {
        return $data;
    }

    /** Runs after a record is created or updated. */
    protected function saved(Model $model, Request $request, bool $created): void {}

    protected function cfg(): array
    {
        return $this->config() + ['search' => [], 'filters' => [], 'per_page' => 15, 'order' => ['id', 'desc'], 'columns' => [], 'fields' => [], 'view' => null];
    }

    /* ---------- List ---------- */

    public function index(Request $request)
    {
        $c = $this->cfg();
        $query = ($c['model'])::query();
        $this->scope($query, $request);

        if ($term = trim((string) $request->query('q'))) {
            $query->where(function (Builder $w) use ($c, $term) {
                foreach ($c['search'] as $column) {
                    $w->orWhere($column, 'like', "%{$term}%");
                }
            });
        }

        foreach ($c['filters'] as $filter) {
            if ($request->filled($filter['name'])) {
                $query->where($filter['name'], $request->query($filter['name']));
            }
        }

        [$column, $direction] = $c['order'];
        $items = $query->orderBy($column, $direction)->orderByDesc('id')->paginate($c['per_page'])->withQueryString();

        return view('admin.resource.index', ['c' => $c, 'items' => $items, 'q' => $request->query('q')]);
    }

    /* ---------- Create / edit ---------- */

    public function create()
    {
        $c = $this->cfg();

        return view('admin.resource.form', ['c' => $c, 'model' => new ($c['model']), 'creating' => true]);
    }

    public function edit(string $id)
    {
        $c = $this->cfg();

        return view('admin.resource.form', ['c' => $c, 'model' => ($c['model'])::findOrFail($id), 'creating' => false]);
    }

    public function store(Request $request, ImageProcessor $images): RedirectResponse
    {
        return $this->save($request, $images, new (($this->cfg())['model']));
    }

    public function update(Request $request, ImageProcessor $images, string $id): RedirectResponse
    {
        $c = $this->cfg();

        return $this->save($request, $images, ($c['model'])::findOrFail($id));
    }

    protected function save(Request $request, ImageProcessor $images, Model $model): RedirectResponse
    {
        $c = $this->cfg();
        $creating = ! $model->exists;

        $validated = $request->validate($this->rules($c['fields'], $creating), $this->messages($c['fields']));

        $data = [];
        foreach ($c['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];

            if (in_array($type, ['image', 'images'], true)) {
                continue; // handled below
            }

            $data[$name] = match ($type) {
                'toggle' => $request->boolean($name),
                'richtext' => $this->richText($field, $validated[$name] ?? null),
                'lines' => collect(preg_split('/\R/', (string) ($validated[$name] ?? '')))->map('trim')->filter()->values()->all(),
                'datetime' => ! empty($validated[$name]) ? Carbon::parse($validated[$name]) : null,
                'number' => ($validated[$name] ?? null) === null || $validated[$name] === '' ? ($field['default'] ?? 0) : $validated[$name],
                default => ($validated[$name] ?? null) === '' ? null : ($validated[$name] ?? null),
            };
        }

        try {
            foreach ($c['fields'] as $field) {
                $name = $field['name'];
                if ($field['type'] === 'image') {
                    $data[$name] = $this->handleImage($request, $images, $model, $field);
                } elseif ($field['type'] === 'images') {
                    $data[$name] = $this->handleImages($request, $images, $model, $field);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['upload' => $e->getMessage()]);
        }

        $data = $this->prepare($data, $creating ? null : $model, $request);
        $model->forceFill($data)->save();
        $this->saved($model, $request, $creating);

        Audit::log($c['route'].($creating ? '.created' : '.updated'), $model, ($creating ? 'Created ' : 'Updated ').$c['singular'].': '.$this->label($model));

        return redirect()->route($c['route'].'.index')->with('success', ucfirst($c['singular']).($creating ? ' created.' : ' saved.'));
    }

    public function destroy(string $id, ImageProcessor $images): RedirectResponse
    {
        $c = $this->cfg();
        $model = ($c['model'])::findOrFail($id);

        foreach ($c['fields'] as $field) {
            if ($field['type'] === 'image') {
                $images->delete($model->{$field['name']});
            } elseif ($field['type'] === 'images') {
                foreach ((array) $model->{$field['name']} as $path) {
                    $images->delete($path);
                }
            }
        }

        $label = $this->label($model);
        $model->delete();
        Audit::log($c['route'].'.deleted', null, 'Deleted '.$c['singular'].': '.$label);

        return redirect()->route($c['route'].'.index')->with('success', ucfirst($c['singular']).' deleted.');
    }

    /* ---------- Internals ---------- */

    protected function label(Model $model): string
    {
        return (string) ($model->title ?? $model->question ?? $model->name ?? '#'.$model->getKey());
    }

    private function rules(array $fields, bool $creating): array
    {
        $rules = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            if ($field['type'] === 'image') {
                $rules[$name] = [($field['required'] ?? false) && $creating ? 'required' : 'nullable', 'file', 'max:12288'];
            } elseif ($field['type'] === 'images') {
                $rules[$name] = ['nullable', 'array', 'max:'.($field['max'] ?? 20)];
                $rules[$name.'.*'] = ['file', 'max:12288'];
            } elseif ($field['type'] === 'toggle') {
                $rules[$name] = ['nullable', 'boolean'];
            } elseif (isset($field['rules'])) {
                $rules[$name] = $field['rules'];
            } else {
                $rules[$name] = ['nullable', 'string', 'max:5000'];
            }
        }

        return $rules;
    }

    /** Sanitised editor HTML. An editor holding only blank lines counts as empty, so "required" still bites. */
    private function richText(array $field, ?string $value): ?string
    {
        $clean = RichText::clean($value);

        if ($clean === null && in_array('required', (array) ($field['rules'] ?? []), true)) {
            throw ValidationException::withMessages([$field['name'] => 'Please fill in '.strtolower($field['label']).'.']);
        }

        return $clean;
    }

    private function messages(array $fields): array
    {
        return collect($fields)->mapWithKeys(fn ($f) => [$f['name'].'.required' => 'Please fill in '.strtolower($f['label']).'.'])->all();
    }

    private function handleImage(Request $request, ImageProcessor $images, Model $model, array $field): ?string
    {
        $name = $field['name'];
        $current = $model->{$name};

        if ($request->boolean('remove_'.$name)) {
            $images->delete($current);
            $current = null;
        }

        if ($request->hasFile($name)) {
            $stored = $images->store($request->file($name), $field['folder'] ?? 'content', $field['width'] ?? 1920);
            $images->delete($current);
            $current = $stored;
        }

        return $current;
    }

    /** @return array<int, string> */
    private function handleImages(Request $request, ImageProcessor $images, Model $model, array $field): array
    {
        $name = $field['name'];
        $current = array_values((array) ($model->{$name} ?? []));

        foreach ((array) $request->input('remove_'.$name, []) as $path) {
            if (in_array($path, $current, true)) {
                $images->delete($path);
                $current = array_values(array_diff($current, [$path]));
            }
        }

        foreach ((array) $request->file($name, []) as $file) {
            if (count($current) >= ($field['max'] ?? 20)) {
                break;
            }
            $current[] = $images->store($file, $field['folder'] ?? 'content', $field['width'] ?? 1920);
        }

        return $current;
    }
}
