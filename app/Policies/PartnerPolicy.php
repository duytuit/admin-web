<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
 
class PartnerPolicy extends BasePolicy
{
    use HandlesAuthorization;
}
