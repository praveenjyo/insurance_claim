<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<body>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<h2>CASHLESS FORM</h2>

<form method="post" action="/claim_submit" style="align-content: center" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <label for="fname">First name:</label><br>
    <input type="text" id="fname" name="fname" placeholder="john" required><br>
    <label for="lname">Last name:</label><br>
    <input type="text" id="lname" name="lname" placeholder="saint" required><br>
    <label for="lname">Insurance No:</label><br>
    <input type="text" id="insurance_no" name="insurance_no" placeholder="in123456j" required><br>
    <label for="lname">Mobile Number:</label><br>
    <input type="text" id="mobile" name="mobile" placeholder="1234567890" min="10" max="12" onkeypress="return isNumberKey(event)" required><br>
    <label for="lname">Hospital Name</label><br>
    <input type="text" id="hospital_name" name="hospital_name" placeholder="hospital name" required><br>
    <label for="lname">Doctor Name</label><br>
    <input type="text" id="doctor_name" name="doctor_name" placeholder="john" required><br>
    <label for="lname">Treatment Name</label><br>
    <input type="text" id="treatment" name="treatment" placeholder="fever" required><br>
    <label for="lname">Total Cost</label><br>
    <input type="text" id="claim_amount" name="claim_amount" placeholder="100"  onkeypress="return isNumberKey(event)" required><br>
    <label for="lname">upload:</label><br>
    <input type="file" id="document" name="document" required><br><br>
    <label  id="message" style="color: red">please fill mandatory fields</label><br><br>
    <input type="submit" value="Submit" onclick="return validate(this);">
</form>

<p></p>

</body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


<script type="text/javascript">
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode != 46 &&(charCode < 48 || charCode > 57)))
            return false;
        return true;
    }
function validate(e)
    {
  var fname=$("#fname").val();
   var  lname=$("#lname").val();
     var insurance_no=$("#insurance_no").val();
      var mobile=$("#mobile").val();
       var hospital=$("#hospital_name").val();
        var doctor=$("#doctor_name").val();
        var  treatment=$("#treatment").val();
         var claim_amount=$("#claim_amount").val();
          var document=$("#document").val();

         if(fname===""||fname==null)
         {
            return  false;
         }
        if(lname===""||lname==null)
        {
            console.log(lname)
            return  false;
        }
        if(insurance_no===""||insurance_no==null)
        {
            return  false;
        }
        if(mobile===""||mobile==null)
        {
            return  false;
        }
        if(hospital===""||hospital==null)
        {
            return  false;
        }
        if(doctor===""||doctor==null)
        {
            return  false;
        }
        if(treatment===""||treatment==null)
        {
            return  false;
        }
        if(claim_amount===""||claim_amount==null)
        {
            return  false;
        }
        if(document===""||document==null)
        {
            return  false;
        }
        return  true;
    }
</script>
</html>
