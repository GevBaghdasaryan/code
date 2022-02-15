<?php

namespace App\Http\Controllers;

use App\Helpers\User\AccessHelper;
use App\Http\Controllers\Controller as Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Foundation\Http\FormRequest;

use App\Services\BaseCreateService;
use App\Services\BaseUpdateService;
use App\Services\BaseDeleteService;
use App\Http\Responses\CRUDResponse;


abstract class BaseCRUDController extends BaseListController
{
    /**
     * @param  FormRequest  $request
     * @return BaseCreateService
     */
    abstract protected function getCreateService(FormRequest $request): BaseCreateService;

    /**
     * @param  int  $id
     * @param  FormRequest  $request
     * @return BaseUpdateService
     */
    abstract protected function getUpdateService(int $id, FormRequest $request): BaseUpdateService;

    /**
     * @param  int  $id
     * @return BaseDeleteService
     */
    abstract protected function getDeleteService(int $id): BaseDeleteService;

    /**
     * @return FormRequest
     */
    abstract protected function getCreateRequest(): FormRequest;

    /**
     * @return FormRequest
     */
    abstract protected function getUpdateRequest(): FormRequest;

    /**
     * @return FormRequest
     */
    abstract protected function getDeleteRequest(): FormRequest;

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function list(Request $request)
    {
        AccessHelper::canOrFail(['viewAny', 'viewPersonal'], $this->getRepository()->getModel());

        $grid = $this->getListGenerator($this->getListBuilder($request))->generate();

        return view(
            $this->getViewsPrefix() . '.list',
            [
                'routeNew' => route($this->getRoutesPrefix() . '_new'),
                'grid' => $grid
            ]
        );
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function new(Request $request)
    {
        AccessHelper::canOrFail('create', $this->getRepository()->getModel());

        $model = $this->getRepository()->getModel();

        return view(
            $this->getViewsPrefix() . '.form',
            [
                'route' => [$this->getRoutesPrefix() . '_create'],
                'model' => $model
            ]
        );
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function create()
    {
        AccessHelper::canOrFail('create', $this->getRepository()->getModel());

        $response = new CRUDResponse();
        $request  = $this->getCreateRequest();

        try {
            $isSave = ($this->getCreateService($request))->run();

            if (!$isSave) {
                throw new \Exception(__('crud.not saved'));
            }

            $response->setMessage(__('crud.saved'));
        } catch (\Exception $e) {
            $response->setException($e);
        }

        return $response->send();
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResource
     * @throws \Exception
     */
    public function get(int $id)
    {
        AccessHelper::canOrFail(['view'], $this->getRepository()->getById($id));

        $model = $this->getRepository()->getById($id);

        return view(
            $this->getViewsPrefix() . '.form',
            [
                'route' => [$this->getRoutesPrefix() . '_update', ['id' => $id]],
                'model' => $model
            ]
        );
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function update($id)
    {
        AccessHelper::canOrFail(['update', 'updatePersonal'], $this->getRepository()->getById($id));

        $response = new CRUDResponse();

        $request  = $this->getUpdateRequest();

        try {
            $isSave = $this->getUpdateService($id, $request)->run();

            if (!$isSave) {
                throw new \Exception(__('crud.not saved'));
            }

            $response->setMessage(__('crud.saved'));
        } catch (\Exception $e) {
            $response->setException($e);
        }

        return $response->send();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function delete($id)
    {
        AccessHelper::canOrFail(['delete', 'deletePersonal'], $this->getRepository()->getById($id));

        $response = new CRUDResponse();

        try {
            $isDelete = $this->getDeleteService($id)->run();

            if (!$isDelete) {
                throw new \Exception(__('crud.not deleted'));
            }

            $response->setMessage(__('crud.deleted'));
        } catch (\Exception $e) {
            $response->setException($e);
        }

        return $response->send();
    }
}
