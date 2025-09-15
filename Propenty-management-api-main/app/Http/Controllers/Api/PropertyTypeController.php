<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    /**
     * Get all active property types in hierarchical structure
     */
    public function index(Request $request)
    {
        try {
            $query = PropertyType::active()->orderBy('sort_order')->orderBy('name');

            // Check if we want hierarchical structure or flat list
            $hierarchical = $request->get('hierarchical', true);

            if ($hierarchical) {
                // Get parent categories with their children
                $parentTypes = $query->parents()->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order')->orderBy('name');
                }])->get();

                return response()->json([
                    'success' => true,
                    'data' => $parentTypes->map(function ($parent) {
                        return [
                            'id' => $parent->id,
                            'name' => $parent->name,
                            'name_ar' => $parent->name_ar,
                            'name_ku' => $parent->name_ku,
                            'slug' => $parent->slug,
                            'description' => $parent->description,
                            'icon' => $parent->icon,
                            'sort_order' => $parent->sort_order,
                            'preferred_name' => $parent->getPreferredName(),
                            'children' => $parent->children->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'name_ar' => $child->name_ar,
                                    'name_ku' => $child->name_ku,
                                    'slug' => $child->slug,
                                    'description' => $child->description,
                                    'icon' => $child->icon,
                                    'sort_order' => $child->sort_order,
                                    'preferred_name' => $child->getPreferredName(),
                                ];
                            }),
                        ];
                    }),
                ]);
            } else {
                // Get flat list of all property types
                $propertyTypes = $query->get();

                return response()->json([
                    'success' => true,
                    'data' => $propertyTypes->map(function ($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'name_ar' => $type->name_ar,
                            'name_ku' => $type->name_ku,
                            'slug' => $type->slug,
                            'description' => $type->description,
                            'icon' => $type->icon,
                            'parent_id' => $type->parent_id,
                            'sort_order' => $type->sort_order,
                            'preferred_name' => $type->getPreferredName(),
                            'full_path' => $type->full_path,
                        ];
                    }),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch property types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all parent property types (root categories)
     */
    public function parents()
    {
        try {
            $parentTypes = PropertyType::active()
                ->parents()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $parentTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'name_ar' => $type->name_ar,
                        'name_ku' => $type->name_ku,
                        'slug' => $type->slug,
                        'description' => $type->description,
                        'icon' => $type->icon,
                        'sort_order' => $type->sort_order,
                        'preferred_name' => $type->getPreferredName(),
                        'children_count' => $type->children()->active()->count(),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch parent property types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get children property types for a specific parent
     */
    public function children(Request $request, $parentId = null)
    {
        try {
            $query = PropertyType::active()->children();

            if ($parentId) {
                $query->where('parent_id', $parentId);
            }

            $childTypes = $query->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $childTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'name_ar' => $type->name_ar,
                        'name_ku' => $type->name_ku,
                        'slug' => $type->slug,
                        'description' => $type->description,
                        'icon' => $type->icon,
                        'parent_id' => $type->parent_id,
                        'sort_order' => $type->sort_order,
                        'preferred_name' => $type->getPreferredName(),
                        'full_path' => $type->full_path,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child property types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a specific property type by ID or slug
     */
    public function show($identifier)
    {
        try {
            // Try to find by ID first, then by slug
            $propertyType = PropertyType::active()
                ->where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->with(['parent', 'children' => function ($query) {
                    $query->active()->orderBy('sort_order')->orderBy('name');
                }])
                ->first();

            if (!$propertyType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property type not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $propertyType->id,
                    'name' => $propertyType->name,
                    'name_ar' => $propertyType->name_ar,
                    'name_ku' => $propertyType->name_ku,
                    'slug' => $propertyType->slug,
                    'description' => $propertyType->description,
                    'icon' => $propertyType->icon,
                    'parent_id' => $propertyType->parent_id,
                    'sort_order' => $propertyType->sort_order,
                    'preferred_name' => $propertyType->getPreferredName(),
                    'full_path' => $propertyType->full_path,
                    'parent' => $propertyType->parent ? [
                        'id' => $propertyType->parent->id,
                        'name' => $propertyType->parent->name,
                        'slug' => $propertyType->parent->slug,
                        'preferred_name' => $propertyType->parent->getPreferredName(),
                    ] : null,
                    'children' => $propertyType->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'name_ar' => $child->name_ar,
                            'name_ku' => $child->name_ku,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'icon' => $child->icon,
                            'sort_order' => $child->sort_order,
                            'preferred_name' => $child->getPreferredName(),
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch property type',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get simple list for dropdowns (id, name pairs)
     */
    public function options(Request $request)
    {
        try {
            $includeParents = $request->get('include_parents', true);
            $includeChildren = $request->get('include_children', true);

            $query = PropertyType::active()->orderBy('sort_order')->orderBy('name');

            if (!$includeParents) {
                $query->children();
            }

            if (!$includeChildren) {
                $query->parents();
            }

            $propertyTypes = $query->get();

            return response()->json([
                'success' => true,
                'data' => $propertyTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'name_ar' => $type->name_ar,
                        'name_ku' => $type->name_ku,
                        'preferred_name' => $type->getPreferredName(),
                        'slug' => $type->slug,
                        'full_path' => $type->full_path,
                        'parent' => $type->parent ? [
                            'id' => $type->parent->id,
                            'name' => $type->parent->name,
                            'name_ar' => $type->parent->name_ar,
                            'name_ku' => $type->parent->name_ku,
                            'slug' => $type->parent->slug,
                            'preferred_name' => $type->parent->getPreferredName(),
                        ] : null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch property type options',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}