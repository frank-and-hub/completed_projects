<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;  
use App\Models\AssociateCommission; 
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;

class CommissionSum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:sum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate sum of commission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      die('hisum');
        \Log::info("Commission sum  sucess!"); 


            $startDateDb=date("Y-m-d");
            $endDateDb=date("Y-m-d");

            $getCurentMont ='11';
            $getCurentYear ='2022'; 
           
           

            $getMember=Member::where('associate_no','!=', '9999999')/*->where('id','>',9893)*/->get(['id']);  

            if(count($getMember) > 0)
            { 
                foreach ($getMember->chunk(5) as  $m1) 
                {  
                   foreach ($m1 as  $m11)                          
                   { 
                   
                         
                     //   $total_commission=AssociateCommission::select(DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('is_add','!=',1)->where('member_id',$m1)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->groupBy(DB::raw('member_id'))->get();   

                     $total_commission=AssociateCommission::select(DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('member_id',$m11->id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->groupBy(DB::raw('member_id'))->get(); 
                   //  print_r($total_commission);die;              
                             
                        foreach ($total_commission as  $v) 
                        { 
                            $count= \App\Models\AssociateCommissionTotal::where('month',$getCurentMont)->where('year',$getCurentYear)->where('member_id',$v->member_id)->count();
                           // echo $count.'ho';die;
                            if($count==0)
                            {
                                

                               $comGet=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->count();
                                if($comGet>0)
                                {
                                   // echo $comGet.'ho';die;
                                    $commission=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->pluck('id')->toArray(); 
                                    
                                    $a=implode( ',',$commission); 
                                    

                                     $commission1=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('commission_amount');

                                     $commission11=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('total_amount'); 


                                       
                                       
                                      $leaser1['member_id'] = $v->member_id;
                                      $leaser1['total_amount'] = $commission11; 
                                      $leaser1['commission_amount'] = $commission1;              
                                      $leaser1['month'] = $getCurentMont; 
                                      $leaser1['year'] = $getCurentYear; 
                                      $leaser1['total_row'] = count($commission); 
                                      $leaser1['commission_id'] = $a;
                                      $leaser1['status'] = 2; 
                                     // print_r($leaser1);die;

                                      $leaserCreate1 = \App\Models\AssociateCommissionTotal::create($leaser1);
                                      
                                      $comDataUpdate = AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('is_add','0')->where('member_id',$v->member_id)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->update([ 'is_add' => 1 ]);
                                }

                            }
                            else
                            { 
                               //  echo $count.'h1';die;
                                $ccc=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->count();
                                if($ccc>0)
                                {

                                    $commission=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->pluck('id')->toArray(); 
                                
                                    $a=implode( ',',$commission); 

                                    $commission1=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('commission_amount');

                                    $commission11=AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('total_amount'); 


                                  
                                    $leaser['total_amount'] = $commission11; 
                                    $leaser['commission_amount'] = $commission1;  
                                    $leaser['total_row'] = count($commission); 
                                    $leaser['commission_id'] = $a; 

                                    $leaserCreate = \App\Models\AssociateCommissionTotal::where('month',$getCurentMont)->where('year',$getCurentYear)->where('member_id',$v->member_id)->update($leaser);

                                    $comDataUpdate1 = AssociateCommission::where('type','>',2)->where('status',1)->where('is_deleted','0')->where('is_add','0')->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->update([ 'is_add' => 1 ]);
                                }
                            }
                        }
                    }
                            
                        
                    
                }
            } 

            


    }
}
