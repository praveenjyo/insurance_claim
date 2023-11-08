
<!DOCTYPE html>
<html lang="en">
<head>
<style>
table {
    font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
    border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
    text-transform:uppercase;
}

tr:nth-child(even) {
background-color: #dddddd;
}

</style>
</head>
<body>

<h2>CASHLESS CLAIMS</h2>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<table>

    <tr>
        <form METHOD="GET" id="dashboard" name="dashboard" action="/dashboard">
            <th></th>
        <th>
            <select name="insurance" id="insurance" onchange="search()">
                <option {{$insurance=="" ? 'selected':""}} value="">SELECT</option>
                @foreach($customers as $cus)
                    <option {{$cus->insurance_no==$insurance ? 'selected':""}} value="{{$cus->insurance_no}}">
                        {{$cus->insurance_no}}</option>
                @endforeach


            </select></th>
        <th></th>
        <th><select name="hospital" id="hospital" onchange="search()">
                <option  value="">SELECT</option>
                @foreach($hospitals as $hpt)
                <option {{$hpt->hospital_name==$hospital ? 'selected':""}} value="{{$hpt->hospital_name}}">{{$hpt->hospital_name}}</option>
                @endforeach
            </select></th>
        <th><select name="doctor" id="doctor" onchange="search()">
                <option  {{$doctor=="" ? 'selected':""}} value="">SELECT</option>
                @foreach($doctors as $hpt)
                    <option {{$hpt->doctor_name==$doctor ? 'selected':""}} value="{{$hpt->doctor_name}}">{{$hpt->doctor_name}}</option>
                @endforeach

            </select></th>
        <th></th>
        <th></th>
        <th></th>
        <th><select name="fraud" id="fraud" onchange="search()">
                <option {{$fraud=="" ? 'selected':""}} value="">SELECT</option>
                <option {{$fraud=="0" ? 'selected':""}} value="0">NO</option>
                <option {{$fraud=="1" ? 'selected':""}} value="1">YES</option>

            </select></th>
        <th>
            <input type="date" id="created_date" name="created_date" value="{{$created_date}}" onchange="search()">
        </th>
           <th> <select name="min_amount" id="min_amount" onchange="search()">
                <option {{$amount=="" ? 'selected':""}} value="">SELECT</option>
                <option {{$amount=="11000" ? 'selected':""}} value="11000">11,000</option>
                <option {{$amount=="15000" ? 'selected':""}} value="15000">15,000</option>

            </select></th>
        <th></th>
        <th><button type="button" id="clearButton">Clear Filters</button></th>
        </form>
    </tr>

  <tr>
        <th>S.No</th>
     <th>INSURANCE_NUMBER</th>
      <th>NAME</th>
      <th>HOSPITAL_NAME</th>
      <th>DOCTOR_NAME</th>
      <th>TREATMENT</th>
      <th>CLAIM_AMOUNT</th>
      <th>STATUS</th>
      <th>IS_FRAUD</th>
      <th>CREATED_DATE</th>
      <th>AUTO APPROVE AMOUNT</th>
      <th>Action</th>
      <th>DOCUMENT</th>
  </tr>
   @php
$total=0;
   $i=1;
@endphp

    @foreach ($data as $item)
        @php
            $total=$total+$item->claim_amount
        @endphp
  <tr>
      <td>{{$i}}</td>
      <td>{{$item->insurance_no}}</td>
      <td>{{$item->fname}}</td>
      <td>{{$item->hospital_name}}</td>
      <td>{{$item->doctor_name}}</td>
      <td>{{$item->treatment}}</td>
      <td>RS: {{$item->claim_amount}}</td>
      <td>{{$item->approved}}</td>
      <td>{{$item->is_fraud=='1' ? "YES" : "NO"}}</td>
      <td>{{$item->created_date}}</td>
      <td>{{$item->amount}}</td>
      @if($item->approved=="approved"||$item->approved=="rejected")
          <td>VERIFIED</td>
      @else
          <td><a href="/approve/{{$item->claim_id}}"><button>APPROVE</button></a><br>
              <a href="/reject/{{$item->claim_id}}"><button>REJECT</button></a><br>

          </td>
      @endif
      <td><a href="/image/{{$item->document_id}}" target="_blank">{{$item->document_id}}</a></td>

  </tr>
        @php
           $i=$i+1;
        @endphp

    @endforeach

    <tr><td></td><td></td><td></td><td></td><td></td><td>Total :</td><td>Rs :{{$total}}</td></tr>

</table>
<div class="col-md-12">
    {{ $data->links('pagination::bootstrap-4') }}  <p style="float: right">Total records: {{ $data->total() }}</p></div>
</div>
</body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        // Get references to HTML elements
        const filterForm = document.getElementById("dashboard");
        const clearButton = document.getElementById("clearButton");

        // Add an event listener to the clear button
        clearButton.addEventListener("click", function () {

            const hospital = document.getElementById("hospital");
            const fraud = document.getElementById("fraud");
            const doctor = document.getElementById("doctor");
            const insurance = document.getElementById("insurance");
            const min = document.getElementById("min_amount");

            hospital.selectedIndex = 0;
            fraud.selectedIndex = 0;
            doctor.selectedIndex = 0;
            insurance.selectedIndex = 0;
            min.selectedIndex=0;
            // Reset the form to clear all filters
            $("form").submit();



        });

    });
function search()
{
    var hospital=$("#hospital").val();
    var fraud=$("#fraud").val();
    var doctor=$("#doctor").val();
    var insurance=$("#insurance").val();

    console.log(insurance);
    $("form").submit();
}
</script>
</html>

