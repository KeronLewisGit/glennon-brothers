<?php

namespace App\Policies;

use App\Enums\Area;
use App\Models\FrameDrawing;
use App\Models\FrameEnquiry;
use App\Models\User;

/** DTE staff and administrators. Drawings show people's homes, so nobody else opens them. */
class FrameEnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->works(Area::Frame);
    }

    public function view(User $user, FrameEnquiry $enquiry): bool
    {
        return $user->works(Area::Frame);
    }

    public function update(User $user, FrameEnquiry $enquiry): bool
    {
        return $user->works(Area::Frame);
    }

    public function openDrawing(User $user, FrameEnquiry $enquiry, FrameDrawing $drawing): bool
    {
        return $drawing->frame_enquiry_id === $enquiry->id && $this->view($user, $enquiry);
    }
}
