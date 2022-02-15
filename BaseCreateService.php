<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Services\ServiceInterface;
use App\Traits\Services\CRUD\SavedModel;

abstract class BaseCreateService implements ServiceInterface
{
    use SavedModel;

    protected $request;
    protected $savedData = [];
    protected $savedModel = null;

    public function __construct(FormRequest $createRequest)
    {
        $this->request = $createRequest;
    }

    /**
     * @return mixed
     */
    public function getRequest(): FormRequest
    {
        return $this->request;
    }

    /**
     * @return Model
     */
    abstract protected function getBaseModel(): Model;

    /**
     * @return array
     */
    public function getSavedData(): array
    {
        if(empty($this->savedData)) {
            $this->savedData = $this->getRequest()->toArray();
        }

        return $this->savedData;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        try {
            DB::transaction(
                function () {
                    $model = $this->getBaseModel();

                    $model->fill(
                        $this->getSavedData()
                    )
                        ->saveOrFail();

                    //Сохранить сохраненную модель в память для дальнейшего использования
                    $this->setSavedModel($model);

                    $this->afterSave();
                }
            );
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }

    /**
     * It is call after had saved base model
     */
    protected function afterSave(): void
    {
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function run(): bool
    {
        return $this->save();
    }
}
