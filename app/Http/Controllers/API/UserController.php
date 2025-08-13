<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    use ResponseTrait;

    // public function getProfile(Request $request)
    // {
    //     try {
    //         $user = User::find(Auth::id());

    //         if (!$user) {
    //             return $this->sendError('User not found.', [], 404);
    //         }

    //         $averageRating = 0;
    //         $totalReviews = 0;
    //         $isSubscribed = false;
    //         $updateDocument = false;

    //         if ($user->role === 'landlord') {
    //             // Get all properties of this landlord
    //             $properties = Property::where('user_id', $user->id)->get();

    //             // Get all reviews for this landlord's properties
    //             $reviews = PropertyReview::whereIn('property_id', $properties->pluck('id'))->get();

    //             // Calculate the total reviews and average rating
    //             $totalReviews = $reviews->count();
    //             if ($totalReviews > 0) {
    //                 $averageRating = $reviews->avg('rating');
    //             }

    //             $landlord = Landlord::where('user_id', $user->id)->first();
    //             if ($landlord && $landlord->subscription_status === 'active') {
    //                 $isSubscribed = true; 
    //             }

    //             if ($landlord && $landlord->insurance_document) {
    //                 $updateDocument = true;
    //             }
    //         }
    //         elseif ($user->role === 'tenant') {
    //             $reviews = TenantReview::where('tenant_id', $user->id)->get();

    //             // Calculate the total reviews and average rating
    //             $totalReviews = $reviews->count();
    //             if ($totalReviews > 0) {
    //                 $averageRating = $reviews->avg('rating');
    //             }

    //             $tenant = Tenant::where('user_id', $user->id)->first();
    //             if ($tenant && $tenant->subscription_status === 'active') {
    //                 $isSubscribed = true; 
    //             }
    //             if ($tenant && $tenant->insurance_document) {
    //                 $updateDocument = true;
    //             }
    //         }

    //         $isVerified = $user->hasVerifiedEmail();

    //         return $this->sendResponse([
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'phone' => $user->phone,
    //             'profile_photo' => $user->profile_photo,
    //             'role' => $user->role,
    //             'about' => $user->about,
    //             "email_verified_at" => $user->email_verified_at,
    //             "is_verified" => $isVerified,
    //             "is_active" => $user->is_active,
    //             "created_at" => $user->created_at,
    //             "updated_at" => $user->updated_at,
    //             'average_rating' => $averageRating,
    //             'total_reviews' => $totalReviews,
    //             'is_subscribe' => $isSubscribed,
    //             'has_insurance_document' => $updateDocument,
    //         ], 'User profile retrieved successfully.');
    //     } catch (\Exception $e) {
    //         Log::error("Error fetching profile: " . $e->getMessage());
    //         return $this->sendError('Error fetching user profile.', [], 500);
    //     }
    // }



    /**
     * Update user's name and email.
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = User::find(Auth::id());

            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email' . $user->id,
                'phone' => 'nullable|string',  
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', 
            ]);

            $data = [];

            if ($request->has('name') && $request->name !== null) {
                $data['name'] = $request->name;
            }
            
            if ($request->has('email') && $request->email !== null) {
                $data['email'] = $request->email;
            }

            if ($request->has('phone') && $request->phone !== null) {
                $data['phone'] = $request->phone;
            }
    
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $profilePhotoPath = $file->store('profile_photos', 'public');
                $data['profile_photo'] = asset('storage/' . $profilePhotoPath);
            }
    
            if (!empty($data)) {
                $user->update($data);
            }
    
            return $this->sendResponse([
                'user' => $user,
            ], 'Profile updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError($e->validator->errors()->first(), [], 400);
        } catch (\Exception $e) {
            Log::error("Error updating profile: " . $e->getMessage());
            return $this->sendError('Error updating profile.' . $e->getMessage(), [], 500);
        }
    }


    public function getUserDetails($userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo' => $user->profile_photo, // assuming profile_photo is stored publicly
                'role' => $user->role,
            ];

            return $this->sendResponse($userDetails, 'User details retrieved successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found: ' . $e->getMessage());
            return $this->sendError('User not found.', [], 404);
        } catch (\Exception $e) {
            Log::error("Unexpected error fetching user details: " . $e->getMessage());
            return $this->sendError('An unexpected error occurred while fetching user details. Please try again later.', [], 500);
        }
    }


    public function getUsers(Request $request)
    {
        try {
            $limit = $request->query('limit', 10); 
            $page = $request->query('page', 1);

            $query = User::where('role', 'user');


            $users = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            if ($users->isEmpty()) {
                return $this->sendError('No users found.', [], 404);
            }

            $users->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'profile_photo' => $user->profile_photo,
                    'name' => $user->name,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ];
            });

            return $this->sendResponse([
                'users' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'last_page' => $users->lastPage(),
                ],
            ], 'All reviews retrieved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Model not found: " . $e->getMessage());
            return $this->sendError('No users found with the specified criteria.'. $e->getMessage(), [], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Database query error: " . $e->getMessage());
            return $this->sendError('Database error while fetching users. Please try again later.'. $e->getMessage(), [], 500);
        } catch (\Exception $e) {
            Log::error("Unexpected error fetching verified users: " . $e->getMessage());
            return $this->sendError('An unexpected error occurred while fetching users. Please try again later.'. $e->getMessage(), [], 500);
        }
    }


    
}
