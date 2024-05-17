<?php

namespace App\Policies;

use App\Models\User;

class CoursesPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function correctAnswer(?User $user, $learningPath)
    {
        return $learningPath->checkFacilitator($user);
    }
}
