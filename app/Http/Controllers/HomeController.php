<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /* submit the claim details*/
    public function claim_submit(Request $request)
    {
        $filename = null;
        $date=date('Y-m-d');
        /*To save the image in local directory*/
        if ($request->hasFile('document')) {
            $filename = $this->saveImagePath($request->file('document'));
        }
        $min_amount = $this->auto_approve_amount_check();

        $id = DB::table('claims')->insertGetId([
            "fname" => $request->fname,
            "last_name" => $request->lname,
            "mobile" => $request->mobile,
            "insurance_no" => $request->insurance_no,
            "hospital_name" => $request->hospital_name,
            "doctor_name" => $request->doctor_name,
            "treatment" => $request->treatment,
            "claim_amount" => $request->claim_amount,
            "document_id" => $filename,
            "created_date" => date('Y-m-d'),
            "created_by" => $request->hospital_name,
            "min_amount_id" => $min_amount->id
        ]);

        /*validate doctor,hospital and customer fraud condition*/
        $doctor=$this->doctor($request->doctor_name,"cashless");
        $hospital=$this->hospital($request->hospital_name,"cashless");
        $customer= $this->past_five_records($request->insurance_no,"cashless");

        $random = (bool)rand(0,1); /*To randomly auto approve*/

        /*checking if all the conditions are satisfied for auto approval randomly*/
        if($doctor&&$hospital&&$min_amount->amount>$request->claim_amount&&$customer&&$random)
        {
            DB::update("update claims set approved='approved', verification_cost=0,is_fraud=0,manual_auto=1,approved_date='$date' where claim_id='$id'");
            session()->flash('success', 'claim approved successfull');

        }else{
           $this->manual_verification($id,"cashless");
               session()->flash('success', 'claim submitted successfull');
        }


        return redirect('/');

    }

    /*To save image in the local folder using unique name*/
    public static function saveImagePath($image)
    {

        $path = public_path() . '/images/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $filename = round(microtime(true) * 1000) . '.' . $image->getClientOriginalExtension();
        $image->move($path, $filename);
        return $filename;

    }

    /*To view the claims of the customers*/
    public function dashboard(Request $request)
    {
        /*To filter the data and pass the filter data to dashboard*/
        $request->session()->put('insurance', $request->insurance);
        $request->session()->put('doctor', $request->doctor);
        $request->session()->put('hospital', $request->hospital);
        $request->session()->put('fraud', $request->fraud);
        $request->session()->put('created_date', $request->created_date);
        $request->session()->put('min_amount', $request->min_amount);

        $insurance = $request->session()->get('insurance');
        $hospital = $request->session()->get('hospital');
        $doctor = $request->session()->get('doctor');
        $fraud = $request->session()->get('fraud');
        $created_date = $request->session()->get('created_date');
        $amount = $request->session()->get('min_amount');


        $data = DB::table('claims')
            ->leftJoin('min_amount', 'claims.min_amount_id', '=', 'min_amount.id')

            ->when($insurance, function ($query, $insurance) {
                return $query->where('insurance_no', $insurance);
            })
            ->when($hospital, function ($query, $hospital) {
                return $query->where('hospital_name', $hospital);
            })
            ->when($doctor, function ($query, $doctor) {
                return $query->where('doctor_name', $doctor);
            })
            ->when($fraud, function ($query, $fraud) {
                return $query->where('is_fraud', $fraud);
            })
            ->when($created_date, function ($query, $created_date) {
                return $query->where('claims.created_date', '=', $created_date);
            })
            ->when($amount, function ($query, $amount) {
                return $query->where('min_amount.amount', '=', $amount);
            })
            ->select("claims.*", 'min_amount.amount')
            ->paginate(10);



        /*Filtering data*/
        $minamounts=DB::table('min_amount')->get();
        $doctors= DB::table('claims')->select("doctor_name")->distinct("doctor_name")->get();
        $hospitals= DB::table('claims')->select("hospital_name")->distinct("hospital_name")->get();
        $customers= DB::table('claims')->select("insurance_no")->distinct("insurance_no")->get();


        return view("dashboard", compact('data', 'insurance', 'hospital', 'doctor', 'fraud', 'created_date', 'amount','doctors','hospitals','minamounts',"customers"));
    }

    /*T o check the past continuous last record are fraud*/
    public function past_five_records($insurance,$type): bool
    {
        $results=null;
        $count=0;
        if ($type == "cashless") {
            $results = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from claims where insurance_no=? ORDER BY created_date DESC LIMIT 5', [$insurance]);
        }

        if ($type == "reimbusrment")
        {
            $results = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from reimbursment where insurance_no=? ORDER BY created_date DESC LIMIT 5', [$insurance]);
        }
        if($results)
        {
            $count = $results[0]->count;
        }
        if ($count < 2) {
            return true;
        }
        return false;
    }

    public function doctor($doctor_name,$type)
    {
        $doctor=null;
        $count=0;
        if ($type == "cashless") {
            $doctor = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from claims where doctor_name=? ORDER BY created_date DESC LIMIT 6', [$doctor_name]);
        }
        if ($type == "reimbusrment") {
            $doctor = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from reimbursment where doctor_name=? ORDER BY created_date DESC LIMIT 6', [$doctor_name]);
        }
        if($doctor)
        {
            $count = $doctor[0]->count;
        }

        if ($count<1) {
            return true;
        }
        return false;
    }
    public function hospital($hospital_name,$type)
    {
        $hospital=null;
        $count=0;
        if ($type == "cashless") {
            $hospital = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from claims where hospital_name=? ORDER BY created_date DESC LIMIT 6', [$hospital_name]);

        }
        if ($type == "reimbusrment") {
            $hospital = DB::select('select sum(case is_fraud when "1" then 1 else 0 end) as count from reimbursment where hospital_name=? ORDER BY created_date DESC LIMIT 6', [$hospital_name]);

        }
        if($hospital)
        {
            $count = $hospital[0]->count;
        }

        if ($count<1) {
            return true;
        }
        return false;
    }


    /*To get the min auto approve amount from table*/
    public function auto_approve_amount_check()
    {
        $min_amount = DB::table("min_amount")->orderBy("created_at", "desc")->first();
        return $min_amount;
    }

    /*To store the image in local folder*/
    public function image(Request $request)
    {
        $image = $request->image;
        $path = public_path() . '/images/' . $image;
        return view("image", compact('image'));

    }

    /*Form to apply reimbursment*/
    public function reimbursment_form()
    {
        return view("reimbursment_form");
    }

    /*This form is used for submitting the reimbusment form*/
    public function reimbursment_submit(Request $request)
    {
        $filename = null;
        /*To save the image in local directory*/
        if ($request->hasFile('document')) {
            $filename = $this->saveImagePath($request->file('document'));
        }
        $date = date('Y-m-d');
        $min_amount = $this->auto_approve_amount_check();
        $id = DB::table('reimbursment')->insertGetId([
            "fname" => $request->fname,
            "last_name" => $request->lname,
            "mobile" => $request->mobile,
            "insurance_no" => $request->insurance_no,
            "hospital_name" => $request->hospital_name,
            "doctor_name" => $request->doctor_name,
            "treatment" => $request->treatment,
            "claim_amount" => $request->claim_amount,
            "document_id" => $filename,
            "created_date" => date('Y-m-d'),
            "created_by" => $request->insurance_no,
            "min_amount_id" => $min_amount->id
        ]);

        /*To validate the different scenarios*/
        $doctor=$this->doctor($request->doctor_name,"reimbusrment");
        $hospital=$this->hospital($request->hospital_name,"reimbusrment");
        $customer= $this->past_five_records($request->insurance_no,"reimbusrment");

        $random = (bool)rand(0,1); /* To get the random value*/

        /*checking if all the conditions are satisfied for auto approval randomly*/
        if($doctor&&$hospital&&$min_amount->amount>$request->claim_amount&&$customer&&$random)
        {
            DB::update("update reimbursment set approved='approved', verification_cost=0,is_fraud=0,manual_auto=1,approved_date='$date' where claim_id='$id'");
            session()->flash('success', 'reimbursment approved successfull');

        }
        else
        {
            $this->manual_verification($id,"reimbursment");
            session()->flash('success', 'reimbursment submitted successfull');
        }


        return redirect('/reimbursment_form');

    }

    /*Dashboard for the reimbusrment data with filters*/
    public function reimbursment_dashboard(Request $request)
    {
        /*To filter the data and pass the filter data to dashboard*/
        $request->session()->put('insurance', $request->insurance);
        $request->session()->put('doctor', $request->doctor);
        $request->session()->put('hospital', $request->hospital);
        $request->session()->put('fraud', $request->fraud);
        $request->session()->put('created_date', $request->created_date);
        $request->session()->put('min_amount', $request->min_amount);

        $insurance = $request->session()->get('insurance');
        $hospital = $request->session()->get('hospital');
        $doctor = $request->session()->get('doctor');
        $fraud = $request->session()->get('fraud');
        $created_date = $request->session()->get('created_date');
        $amount = $request->session()->get('min_amount');


        $data = DB::table('reimbursment')
            ->leftJoin('min_amount', 'reimbursment.min_amount_id', '=', 'min_amount.id')
            ->when($insurance, function ($query, $insurance) {
                return $query->where('insurance_no', $insurance);
            })
            ->when($hospital, function ($query, $hospital) {
                return $query->where('hospital_name', $hospital);
            })
            ->when($doctor, function ($query, $doctor) {
                return $query->where('doctor_name', $doctor);
            })
            ->when($fraud, function ($query, $fraud) {
                return $query->where('is_fraud', $fraud);
            })
            ->when($created_date, function ($query, $created_date) {
                return $query->where('reimbursment.created_date', '=', $created_date);
            })
            ->when($amount, function ($query, $amount) {
                return $query->where('min_amount.amount', '=', $amount);
            })
            ->select("reimbursment.*", 'min_amount.amount')
            ->paginate(10);

        /*filter data*/
        $minamounts=DB::table('min_amount')->get();
        $doctors= DB::table('reimbursment')->select("doctor_name")->distinct("doctor_name")->get();
        $hospitals= DB::table('reimbursment')->select("hospital_name")->distinct("hospital_name")->get();
        $customers= DB::table('reimbursment')->select("insurance_no")->distinct("insurance_no")->get();

        return view("reimbursment_dashboard", compact('data', 'insurance', 'hospital', 'doctor', 'fraud', 'created_date', 'amount','doctors','hospitals','customers'));
    }

    /*To Approve the cashless claim using claim_id*/
    public function approve_claim($id)
    {
        $date = date('Y-m-d');
        DB::update("update claims set approved='approved', verification_cost=0,is_fraud=0,approved_date='$date' where claim_id='$id'");
        session()->flash('success', 'claim approved successfull');
        return redirect('/dashboard');
    }

    /*To Reject the cashless claim using claim_id*/
    public function reject_claim($id)
    {
        $date = date('Y-m-d');
        DB::update("update claims set approved='rejected', verification_cost=0,is_fraud=1 where claim_id='$id'");
        session()->flash('success', 'claim rejected successfull');
        return redirect('/dashboard');
    }

    /*To Approve the reimbursment  using claim_id*/
    public function approve_rit($id)
    {
        $date = date('Y-m-d');
        DB::update("update reimbursment set approved='approved', verification_cost=0,is_fraud=0,approved_date='$date' where claim_id='$id'");
        session()->flash('success', 'reimbursment approved successfull');
        return redirect('/reimbursment_dashboard');
    }

    /*To Reject the reimbursment  using claim_id*/
    public function reject_rit($id)
    {
        $date = date('Y-m-d');
        DB::update("update reimbursment set approved='rejected', verification_cost=0,is_fraud=1,approved_date='$date' where claim_id='$id'");
        session()->flash('success', 'reimbursment rejected successfull');
        return redirect('/reimbursment_dashboard');
    }

    /*To send claim to manual verification for reimbursment*/
    public function manual_verification($id,$type)
    {
        $date = date('Y-m-d');
        DB::table('manual_verification')->insertGetId([
            "claims_id" => $id,
            "date_created" => $date,
            "assigned_to" => "akash",
            "status" => "under_verification",
            "cost" => 8500,
            "claim_type" => $type
        ]);
        if ($type == "cashless") {
            DB::update("update claims set approved='manual_verification', verification_cost=8500,is_fraud=0 where claim_id='$id'");
        }
        if ($type == "reimbusrment")
        {
            DB::update("update reimbursment set approved='manual_verification', verification_cost=8500,is_fraud=0 where claim_id='$id'");
         }

        return true;
    }



}
