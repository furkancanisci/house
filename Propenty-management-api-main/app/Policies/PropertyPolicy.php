<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any properties.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view the property listings
        return true;
    }

    /**
     * Determine whether the user can view the property.
     */
    public function view(?User $user, Property $property): bool
    {
        // Anyone can view active and available properties
        if ($property->status === 'active' && $property->is_available) {
            return true;
        }

        // Property owners can view their own properties regardless of status
        if ($user && $property->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create properties.
     */
    public function create(User $user): bool
    {
        // Only property owners can create properties
        return $user->isPropertyOwner() && $user->is_verified && $user->is_active;
    }

    /**
     * Determine whether the user can update the property.
     */
    public function update(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can update properties.');
        }

        if (!$user->is_verified) {
            return Response::deny('Please verify your email address to update properties.');
        }

        if (!$user->is_active) {
            return Response::deny('Your account is inactive. Please contact support.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only update your own properties.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the property.
     */
    public function delete(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can delete properties.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only delete your own properties.');
        }

        // Prevent deletion if property has active applications/inquiries
        // This would be implemented based on your business logic
        
        return Response::allow();
    }

    /**
     * Determine whether the user can restore the property.
     */
    public function restore(User $user, Property $property): bool
    {
        return $user->isPropertyOwner() && $property->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the property.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        return $user->isPropertyOwner() && $property->user_id === $user->id;
    }

    /**
     * Determine whether the user can publish the property.
     */
    public function publish(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can publish properties.');
        }

        if (!$user->is_verified) {
            return Response::deny('Please verify your email address to publish properties.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only publish your own properties.');
        }

        if ($property->status !== 'draft') {
            return Response::deny('Only draft properties can be published.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can feature the property.
     */
    public function feature(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can feature properties.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only feature your own properties.');
        }

        // Add business logic for featuring (e.g., payment required, limits, etc.)
        
        return Response::allow();
    }

    /**
     * Determine whether the user can view analytics for the property.
     */
    public function viewAnalytics(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can view analytics.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only view analytics for your own properties.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can favorite the property.
     */
    public function favorite(User $user, Property $property): Response
    {
        if ($property->user_id === $user->id) {
            return Response::deny('You cannot favorite your own property.');
        }

        if ($property->status !== 'active' || !$property->is_available) {
            return Response::deny('You can only favorite active and available properties.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can contact the property owner.
     */
    public function contact(User $user, Property $property): Response
    {
        if ($property->user_id === $user->id) {
            return Response::deny('You cannot contact yourself about your own property.');
        }

        if ($property->status !== 'active' || !$property->is_available) {
            return Response::deny('You can only contact owners of active and available properties.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can upload media for the property.
     */
    public function uploadMedia(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can upload media.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only upload media for your own properties.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete media from the property.
     */
    public function deleteMedia(User $user, Property $property): Response
    {
        if (!$user->isPropertyOwner()) {
            return Response::deny('Only property owners can delete media.');
        }

        if ($property->user_id !== $user->id) {
            return Response::deny('You can only delete media from your own properties.');
        }

        return Response::allow();
    }
}
