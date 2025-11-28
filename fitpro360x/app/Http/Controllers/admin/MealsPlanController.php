<?php

namespace App\Http\Controllers\admin;

use App\Models\MealDietPreference;
use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\MealsPlan;
use App\Models\MealsPlanIngredients;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\File; // Add at top if not already
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MealsPlanController extends Controller
{
    use Common_trait;
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $mealType = $request->input('meal_type');

        $query = MealsPlan::query();

        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Redirect to named route if current page is invalid
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.mealsPlanIndex', ['limit' => $limit]);
        }

        if ($search) {
            $searchColumns = ['title', 'description', 'type']; // Columns to search in
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }


        // Add meal type filter if provided
        if ($mealType) {
            $typeMapping = [
                'Breakfast' => 1,
                'Lunch' => 2,
                'Dinner' => 3
            ];

            if (array_key_exists($mealType, $typeMapping)) {
                $query->where('type', $typeMapping[$mealType]);
            }
        }

        $mealDietPreference = MealDietPreference::get();

        // $this->applySearch($query, $search); // Apply only search logic

        $mealPlans = $query->orderBy('id', 'desc')->paginate($limit);

        if ($request->has('search')) {
            $mealPlans->appends(['search' => $search]);
        }

        // Append limit parameter to pagination links
        $mealPlans->appends(['limit' => $limit]);

        return view('admin.meals-plan.index', compact('mealPlans', 'mealDietPreference'));
    }

    public function mealsPlanSave(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:1200',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'type' => 'required|string',
            'diet_preference' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:255',
            'proteins' => 'nullable|string',
            'carbohydrates' => 'nullable|string',
            // 'fat' => 'nullable|string',
        ]);


        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->file_upload($request->file('image'), 'meals');
            }

            $meal = MealsPlan::create([
                'title' => Str::title(preg_replace('/\s+/', ' ', trim($request->title))),
                'description' => $request->description,
                'image' => $imagePath,
                'type' => $request->type,
                'diet_preference' => $request->diet_preference,
                'protein' => $request->proteins,
                'carbs' => $request->carbohydrates,
                'fat' => $request->fat,
            ]);

            foreach ($request->ingredients as $ingredient) {
                MealsPlanIngredients::create([
                    'meal_plans_id' => $meal->id,
                    'ingredient' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'],
                ]);
            }


            DB::commit();
            return response()->json(['success' => true, 'message' => 'Meal plan saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error saving meal plan.'], 500);
        }
    }



    public function mealsPlanEdit($id)
    {
        $mealsPlan = MealsPlan::with('ingredients')->findOrFail($id);

        return response()->json([
            'title' => $mealsPlan->title,
            'type' => $mealsPlan->type,
            'diet_preference' => $mealsPlan->diet_preference,
            'description' => $mealsPlan->description,
            'image' => $mealsPlan->image,
            'proteins' => $mealsPlan->protein,
            'carbohydrates' => $mealsPlan->carbs,
            'fat' => $mealsPlan->fat,
            'ingredients' => $mealsPlan->ingredients->map(function ($ingredient) {
                return [
                    'name' => $ingredient->ingredient,
                    'quantity' => $ingredient->quantity
                ];
            })
        ]);
    }

    public function mealsPlanUpdate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:1200',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'type' => 'required|string',
            'diet_preference' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:255',
            'proteins' => 'nullable|string',
            'carbohydrates' => 'nullable|string',
            'fat' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $meal = MealsPlan::findOrFail($id);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($meal->image && File::exists(public_path($meal->image))) {
                    File::delete(public_path($meal->image));
                }
                $imagePath = $this->file_upload($request->file('image'), 'meals');
                $meal->image = $imagePath;
            }

            // Update meal plan details
            $meal->update([
                'title' => Str::title(preg_replace('/\s+/', ' ', trim($request->title))),
                'description' => $request->description,
                'type' => $request->type,
                'diet_preference' => $request->diet_preference,
                'protein' => $request->proteins,
                'carbs' => $request->carbohydrates,
                'fat' => $request->fat,
            ]);

            // Sync ingredients
            $existingIngredientIds = $meal->ingredients()->pluck('id')->toArray();
            $submittedIngredientIds = [];

            foreach ($request->ingredients as $ingredientData) {
                if (isset($ingredientData['id'])) {
                    // Update existing ingredient
                    $ingredient = MealsPlanIngredients::find($ingredientData['id']);
                    if ($ingredient) {
                        $ingredient->update([
                            'ingredient' => $ingredientData['name'],
                            'quantity' => $ingredientData['quantity'],
                        ]);
                        $submittedIngredientIds[] = $ingredient->id;
                    }
                } else {
                    // Create new ingredient
                    $newIngredient = MealsPlanIngredients::create([
                        'meal_plans_id' => $meal->id,
                        'ingredient' => $ingredientData['name'],
                        'quantity' => $ingredientData['quantity'],
                    ]);
                    $submittedIngredientIds[] = $newIngredient->id;
                }
            }

            // Delete removed ingredients
            $ingredientsToDelete = array_diff($existingIngredientIds, $submittedIngredientIds);
            MealsPlanIngredients::whereIn('id', $ingredientsToDelete)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Meal plan updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error updating meal plan.'], 500);
        }
    }



    public function mealsPlanDelete($id)
    {
        DB::transaction(function () use ($id) {
            $meal = MealsPlan::findOrFail($id);
            $meal->ingredients()->delete(); // Delete associated ingredients
            $meal->delete(); // Delete the meal plan
        });

        return response()->json(['success' => true]);
    }
}
