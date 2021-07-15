<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\DoubleEntryAccountCategory;
use Yajra\DataTables\Facades\DataTables; 

class DoubleEntryAccountCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {   
        
        return view('accounting::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {  
        return view('accounting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        // if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
        //     abort(403, 'Unauthorized action.');
        // }
        try{

            $request->validate([
                'account_type' => 'required',
                'category_code' => ['required'],
                'category_name' => ['required','unique:acc_account_categories,category_name']
            ]);


            $new_category = new DoubleEntryAccountCategory();

            $new_category ->business_id = $business_id;

            $new_category ->account_type_id = $request->account_type;
            $new_category ->category_code = $request->category_code;

            $new_category ->category_name = $request->category_name;
            $new_category ->sort_order = 1;
            
            $new_category ->save();



            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success')
            ];

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        } 
        
        return redirect()->action('\Modules\Accounting\Http\Controllers\AccountingController@masterData')->with('status', $output);

    }

    public function getData(Request $request)
    {   

        $business_id = $request->session()->get('user.business_id');

        if($request->ajax()){

            //join('double_entry_account_types as AT','AT.id','=','double_entry_account_categories.account_type_id')            
        
            $account_types_data =  DoubleEntryAccountCategory::where('business_id',$business_id)
                                                                ->with('account_type')
                                                                ->select('acc_account_categories.*');
            
            return Datatables::of($account_types_data)
                            ->addColumn('action', '') 
                            ->editColumn('type', function ($row) {                                            
                                return $row->account_type->type;
                            }) 
                            // ->filterColumn('category_name', function ($query, $keyword) {
                            //     $query->whereRaw("CONCAT(double_entry_account_categories.category_code, ' - ', double_entry_account_categories.category_name, ' - ') like ?", ["%{$keyword}%"]);
                            // })                            
                            ->rawColumns(['action'])
                            ->make(true);

        }
        return null;
    }

    public function getCategories(Request $request)
    {
        if (!empty($request->input('type_id'))) {

            $type_id = $request->input('type_id');
            $business_id = $request->session()->get('user.business_id');

            $categories = DoubleEntryAccountCategory::where('business_id', $business_id)
                        ->where('account_type_id', $type_id)
                        ->select(['category_name', 'id'])
                        ->get();

            $html = '<option value="">None</option>';

            if (!empty($categories)) {
                //$html ='';
                foreach ($categories as $category) {
                    $html .= '<option value="' . $category->id .'">' .$category->category_name . '</option>';
                }
            }
            echo $html;
            exit;
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('accounting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('accounting::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
