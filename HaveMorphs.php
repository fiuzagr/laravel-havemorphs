<?php

namespace App;

use \Closure;
use Illuminate\Database\QueryException;

trait HaveMorphs
{

    public function createOrUpdateMorphs ($data)
    {
        if (isset($this->morphs)) {
            foreach ($this->morphs as $morph) {
                if (!array_key_exists($morph, $data)) {
                    continue;
                }

                $data[$morph] = array_filter($data[$morph]);
                $morphItem = $this->{$morph}();

                // get only updated data
                $onlyUpdated = collect($data[$morph])->filter(function ($value) {
                    return array_key_exists('id', $value) && !!$value['id'];
                })->keyBy('id')->all();

                // update or delete
                $morphItem->get()->map(function ($item) use ($onlyUpdated) {
                    if (array_key_exists($item->id, $onlyUpdated)) {
                        if ($onlyUpdated[$item->id]) {
                            $item->update($onlyUpdated[$item->id]);
                        }
                    } else {
                        // TODO Remover morphs aninhados
                        $item->delete();
                    }
                });

                // create and return all data with id
                $createdAndUpdated = collect($data[$morph])->map(function ($value) use ($morphItem) {
                    if (! array_key_exists('id', $value) || !$value['id']) {
                        try {
                            $value['id'] = $morphItem->create($value)->id;
                        } catch (QueryException $e) {
                            // ignore query errors
                        }
                    }

                    return $value;
                })->keyBy('id')->all();

                // inherited morphs
                $morphItem->get()->map(Closure::bind(function ($item) use ($createdAndUpdated) {
                    if (array_key_exists($item->id, $createdAndUpdated)) {
                        if (method_exists($item, 'createOrUpdateMorphs') && $createdAndUpdated[$item->id]) {
                            $item->createOrUpdateMorphs($createdAndUpdated[$item->id]);
                        }
                    }
                }, $this));
            }
        }
    }
}
