<?php
/**
 * Copyright 2015 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

namespace repositories\marketplace;

use models\marketplace\Consultant;
use models\marketplace\IConsultant;
use models\marketplace\repositories\IConsultantRepository;
use utils\services\ILogService;

/**
 * Class EloquentConsultantRepository
 * @package repositories\marketplace
 */
class EloquentConsultantRepository extends EloquentCompanyServiceRepository implements IConsultantRepository {

    /**
     * @param Consultant  $consultant
     * @param ILogService $log_service
     */
    public function __construct(Consultant $consultant, ILogService $log_service){
        $this->entity       = $consultant;
        $this->log_service  = $log_service;
    }
}