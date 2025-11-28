<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {

        $active_page = "category";
        $page_title = "Prority";
        //
        $breadcrumbs = [['name' => 'Categories', 'route' => route('admin.category.index')]];
        return view('admin.category.priority', compact('active_page', 'page_title', 'breadcrumbs'));
    }

    public function dt_list(Request $request)
    {


        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');

        switch ($request->type) {
            case 'all':
                $query = Category::query();
                break;
            case 'parent':
                $query = Category::where('type', 'parent')->where('special_category', 0)->where('active', 1);
                break;

            case 'no-child':
                $query = Category::where('type', 'no-child')->where('special_category', 0)->where('active', 1);
                break;

            case 'all_seasonal':
                $query = Category::where('special_category', 1)->where('active', 1);
                break;

            case 'parent_special':
                $query = Category::where('type', 'parent')->where('special_category', 1);
                break;

            case 'standalone_special':
                $query = Category::where('type', 'no-child')->where('special_category', 1);
                break;
            default:
                $query = Category::query();
        }

        $recordsTotal = $query->count();
        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();
        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            // $d->is_set_as_home = $d->is_set_as_home ? 'Yes' : 'No';
            $special_category = ($d->special_category == 1) ? ' (Seasonal Category)' : null;
            $d->priority = $d->priority ?? 'N/A';
            // $d->show = ($d->is_set_as_home == '1') ? "Display as parent category card (in collage)" : "Display as separate category carousel";
            $d->show =  'N/A';
            if (($d->is_set_as_home == '1') && ($d->type == 'parent')) {
                $d->show = "Display as parent category card (in collage)";
            }

            if (($d->is_set_as_home == '1') && ($d->type == 'no-child')) {
                $d->show = "Display as separate park carousel";
            }

            if (($d->is_set_as_carousel == '1') && ($d->type == 'parent')) {
                $d->show = "Display as separate category carousel";
            }
            if (($d->is_set_as_carousel == '1') && ($d->type == 'no-child')) {
                $d->show = 'Display in "More Categories" carousel';
            }

            if (($d->is_set_as_carousel == '1') && ($d->type == 'no-child')) {
                $d->show = 'Display in "More Categories" carousel';
            }

            if (($d->is_display_by_itself == '1') && ($d->type == 'no-child')) {
                $d->show = 'Display as standalone category card (by itself)';
            }

            if (($d->is_display_by_itself == '1') && ($d->type == 'parent')) {
                $d->show = ' Display as parent category card (by itself)';
            }

            $d->type = $d->type == 'no-child' ? 'Standalone' . $special_category : ucwords($d->type) . $special_category;

            $d->name =  "<img src='" . $image  .
                "'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . ucfirst($d->name);
            $editRoute = route('admin.category.edit', $d->id);
            // $ShowChlidRoute =  ($d->type != 'Standalone') ? route('admin.subcategory.index', $d->id) : null;
            $deleteRoute = route('admin.delete.category', $d->id);

            $statusRoute = route('admin.update.status', $d->id);
            $status = ($d->active == 1) ? 'checked' : '';
            // $d->total_child_categories = Subcategory::where('category_id', $d->id)->count();
            $id = $d->id;
            // $d->action = view('admin.category._dt_action', compact('d'))->render();

        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function updatePriority(Request $request)
    {
        if ($request->ajax()) {
            $update = Category::where('id', $request->id)->update(['priority' => $request->priority]);
            if ($update) {
                return response()->json([
                    'status' => 'success',
                    'msg' => __('admin.priority_update')
                ]);
            }
        }
    }
}
