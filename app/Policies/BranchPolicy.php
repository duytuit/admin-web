<?php

namespace App\Policies;

use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy extends BasePolicy
{
    use HandlesAuthorization;
}
