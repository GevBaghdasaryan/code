<?php

namespace App\Services\Partner;

use App\Helpers\User\AccessHelper;
use App\Models\Partner;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\User\CreateService as UserCreateService;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseCreateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CreateService extends BaseCreateService
{
    /**
     * @return Model
     */
    protected function getBaseModel(): Model
    {
        return new Partner();
    }

    /**
     * @return array
     */
    public function getSavedData(): array
    {
        $savedData = parent::getSavedData();

        $savedData['active']         = (boolean)$this->getRequest()->get('active');
        $savedData['faq_access']     = (boolean)$this->getRequest()->get('faq_access');
        $savedData['display_on_map'] = (boolean)$this->getRequest()->get('display_on_map');
        $savedData['can_buy_test']   = (boolean)$this->getRequest()->get('can_buy_test');

        return $savedData;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        DB::beginTransaction();

        if (!$partner_role = Role::select('id')->where('name', User::ROLE_PARTNER)->first()) {
            $partner_role = Role::findOrCreate(User::ROLE_PARTNER, 'web');
        }

        $this->getRequest()->merge(['status' => 1, 'role' => $partner_role->id]);
        $user = (new UserCreateService($this->getRequest()))->savePartnerUser();
        $this->getRequest()->merge(['user_id' => $user->id, 'password' => Hash::make($this->getRequest()->get('password'))]);

        $model = $this->getBaseModel();
        $model->fill(
            $this->getSavedData()
        )
            ->save();

        if (empty($model->id)) {
            DB::rollBack();
            throw new \Exception(__('crud.not saved'));
        }

        $this->setSavedModel($model);

        //Enable sync teachers and scools only to admin
        if(AccessHelper::isCan('viewAny', $this->getBaseModel())) {
            $this->syncTeaches();
            $this->syncSchools();
        }

        DB::commit();

        return true;
    }

    /**
     * @return bool
     */
    protected function syncTeaches(): bool
    {
        try {
            $teachers = [];

            if ($this->getRequest()->has('teachers')) {
                $teachers = $this->getRequest()->get('teachers');
            }

            $this->getSavedModel()->teachers()->sync($teachers);
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function syncSchools(): bool
    {
        try {
            if ($this->getRequest()->has('schools')) {
                School::find($this->getRequest()->get('schools'))->each(
                    function ($school) {
                        $school->partner_id = $this->getSavedModel()->id;
                        $school->save();
                    }
                );
            }
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }
}
