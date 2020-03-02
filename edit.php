<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login");
  exit();
}
include "entope.php";
include "functions.php";

if (isset($_POST['donatebtn'])) {
  $domination_price = $_POST['donation'];
  $domination_price = convertNumbers($domination_price, false);
  if ($domination_price !== "") {
    $query = "INSERT INTO domination (value) VALUE ('{$domination_price}')";
    $donate = mysqli_query($connection, $query);
    if (!$donate) {
      die(mysqli_error($connection));
    } else {
      $query = "SELECT count(id) as cid FROM customer WHERE active = '1'";
      $count_actives = mysqli_query($connection, $query);
      if (!$count_actives) {
        die(mysqli_error($connection));
      } else {
        if ($row = mysqli_fetch_assoc($count_actives)) {
          $c_act = $row['cid'];
          $query = "SELECT price FROM bread ORDER BY date DESC LIMIT 1";
          $select_price = mysqli_query($connection, $query);
          if (!$select_price) {
            die(mysqli_error($connection));
          } else {
            if ($row = mysqli_fetch_assoc($select_price)) {
              $breads_donate = $domination_price / ($c_act * $row['price']);
              $query = "SELECT * FROM customer WHERE active = '1'";
              $select_customers = mysqli_query($connection, $query);
              if (!$select_customers) {
                die(mysqli_error($connection));
              } else {
                while ($row1 = mysqli_fetch_assoc($select_customers)) {
                  $cus_id = $row1['id'];
                  $cus_rem = $row1['remaining'];
                  $cus_rem = $cus_rem + $breads_donate;
                  $query = "SELECT remaining FROM customer WHERE id = '{$cus_id}'";
                  $find_cus = mysqli_query($connection, $query);
                  if (!$find_cus) {
                    die(mysqli_error($connection));
                  } else {
                    if ($row_cus = mysqli_fetch_assoc($find_cus)) {
                      $cus_rem = $row_cus['remaining'];
                      $new_rem = $cus_rem + $breads_donate;
                      $query = "UPDATE customer SET remaining = {$new_rem} WHERE id = '{$cus_id}'";
                      $upd_rem = mysqli_query($connection, $query);
                      if (!$upd_rem) {
                        die(mysqli_error($connection));
                      }
                    }
                  }
                }
                $message = "کمک مالی لحاظ شد و تعداد مناسب به هر خانوار اضافه گردید.";
                echo "<script type='text/javascript'>alert('$message');</script>";

              }
            }
          }
        }
      }
    }
  } else {
    $message = "فیلد مبلغ اهدایی خالی است.";
    echo "<script type='text/javascript'>alert('$message');</script>";
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>سامانه مدیریت سهمیه نان | ویرایش قیمت</title>

  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="./stylesheets/main.css">
</head>
<body>

<button onclick="topFunction()" id="myBtn" title="بالا">&uarr;</button>

<nav id="nav-cus" class="navbar fixed-top">
  <p class="nav nav-item mx-auto l-align align-items-center" style="direction: ltr; text-align: right;">
    (تومان
    <span class="mx-2 l-align" id="r_money">۰</span>
    )نان کمک کنید. باقیمانده
    <span class="mx-4 l-align" id="t_bread" style="font-size: 28px;">۰</span>
    شما می‌توانید
  </p>
</nav>

<nav id="nav-ext" class="navbar fixed-bottom">
  <label class="nav nav-item mx-auto">
    <span class="mr-4" id="extra">۰</span>
    <span class="r-align">باقیمانده: </span>
  </label>
</nav>

<div class="mx-auto mb-4" style="width: 95% !important;">
  <div class="mt-5">
    <div class="card card-body">
      <div class="card-body">
        <a href="index" class="float-left btn btn-outline-dark">برگرد</a>
        <a href="editBreadPrice" class="float-left btn btn-outline-dark ml-1">قیمت نان</a>
        <a href="addMember" class="float-left btn btn-outline-dark ml-1">اضافه‌کردن خانوار</a>
        <a href="logout" class="float-left btn btn-danger ml-1">خروج</a>
        <h4 class="card-title mb-4 mt-1 text-right">اهداییه</h4>
        <hr>
        <div class="col align-items-center rounded mb-2">
          <div class="row r-align mr-auto">
            <small class="col-2 my-1 r-align text-light bg-danger p-2 rounded">
              قیمت نان
              <b class="m-auto" style="font-size: 20px">
                <?php echo convertNumbers(getBreadPrice(), true); ?>
              </b>
              تومان
            </small>
          </div>
        </div>

        <?php include "customersStats.php"; ?>

        <form action="donate" method="post" enctype="multipart/form-data">
          <input type="text" value="" name="a-extra" id="a-extra" hidden>
          <input type="text" value="" name="a-rmoney" id="a-rmoney" hidden>
          <input type="text" value="" name="a-tbread" id="a-tbread" hidden>

          <div class="row m-auto d-flex align-items-center" style="direction: rtl;">
            <div class="radio col-lg-3 col-sm-12">
              <label>وارد کردن مبلغ
                <input type="radio" name="cdon" id="mcd" onchange="showInputField();" checked>
              </label>
            </div>
            <div class="radio col-lg-3 col-sm-12">
              <label>وارد کردن تعداد
                <input type="radio" name="cdon" id="acd" onchange="showInputField();">
              </label>
            </div>

            <div class="mt-2 mb-2 col d-flex">
              <input class="col-lg-4 col-sm-4 form-control my-auto" id="money_custom_donation"
                     name="money_custom_donation"
                     onkeypress="validate(event);" onclick="this.select();"
                     placeholder="مبلغ را وارد کنید" onkeyup="fillFields();"
                     onpaste="return false;">
              <input class="col-lg-4 col-sm-4 form-control my-auto" id="amount_custom_donation"
                     name="amount_custom_donation"
                     onkeypress="validate(event);" onclick="this.select();"
                     placeholder="تعداد را وارد کنید" onkeyup="fillFieldsCount();"
                     onpaste="return false;"
                     hidden>
              <br>
            </div>
          </div>

          <div class="row my-3 r-align align-items-center d-flex mx-auto">
            <div class="radio col-lg-3 col-sm-12 l-align align-self-center my-auto">
              <label class="mr-2" for="use-bank">استفاده از انبار</label>
              <input class="m-auto" type="checkbox" name="use-bank" id="use-bank" onchange="useBank();">
            </div>
            <div class="radio col-lg-3 col-sm-12 l-align align-self-center my-auto">
              <label class="mr-2" for="use-bank-all">برداشت کل</label>
              <input class="m-auto" type="checkbox" name="use-bank-all" id="use-bank-all" onchange="withdrawalAll();"
                     disabled>
            </div>
            <div class="col-lg-2 col-sm-6">
              <input class="form-control bg-dark text-light border-dark" type="text" name="bank" id="bank"
                     value="<?php echo convertNumbers(getBreadsInBank(), true); ?>"
                     disabled>
            </div>
            <b class="div-mark">|</b>
            <div class="col-lg-2 col-sm-6">
              <input class="form-control" type="text" id="bank_usage" name="bank_usage"
                     onkeypress="validate(event);" placeholder="...تعداد برداشت از انبار"
                     onkeyup="validateBankUsage(this.value);" onpaste="return false;"
                     disabled>
            </div>
            <div class="col-lg-1 col-sm-12">
              <small class="border border-danger text-danger bank-error" id="bank-msg" hidden>
                مقدار نامعتبر!
              </small>
            </div>
          </div>

          <div class="col">
            <div class="row r-align">
              <small class="col-lg-3 col-sm-12 r-align mb-2  text-muted">
                چک باکس راست: انتخاب همه
              </small>
              <small class="col-lg-3 col-sm-12 r-align mb-2 text-muted">
                چک باکس چپ: انتخاب همه VIPها
              </small>
            </div>
          </div>

          <table class="table table-bordered table-striped">
            <tr>
              <th style="width: 5%;">VIP</th>
              <th style="width: 5%;">عائله</th>
              <th>نام خانوادگی</th>
              <th>نام</th>
              <th style="width: 12%;">کد</th>
              <th style="width: 8%;">.ت.ب</th>
              <th style="width: 10%;">تعداد</th>
              <th style="text-align: right; width: 3%; margin: 0"><input type="checkbox" id="select_all_vip"
                                                                         onclick="toggleVipChecks();">
              </th>
              <th style="text-align: right; width: 3%; margin: 0"><input type="checkbox" id="select_all"
                                                                         onclick="toggleAllChecks();">
              </th>
            </tr>
            <?php
            $query = "SELECT * FROM customer";
            $select_menu_items_vip = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($select_menu_items_vip)) {
            if ($row['vip'] === '1') { ?>
            <tr>
              <td id="vip[<?php echo $row['id']; ?>]"><?php echo "<h5 style='color: #5cb85c'>&#10003;</h5>"; ?></td>
              <td id="family[<?php echo $row['id']; ?>]"><?php echo convertNumbers($row['family'], true); ?></td>
              <td><?php echo $row['last_name']; ?></td>
              <td><?php echo $row['first_name']; ?></td>
              <td><?php echo convertNumbers($row['id'], true); ?>
              <td><?php echo convertNumbers($row['remaining'], true); ?>
              <td><input class="form-control" type="text" name="donate_count[<?php echo $row['id']; ?>]"
                         id="donate_count[<?php echo $row['id']; ?>]" value="<?php
                echo convertNumbers(0, true);
                ?>" onkeypress="validate(event);" onkeyup="changeFieldsOnInput();" onclick="this.select();"
                         onpaste="return false;"
                         disabled>
              </td>
              <td colspan="2"><input class="mx-auto" type="checkbox" name="donate_check[<?php echo $row['id']; ?>]"
                                     id="donate_check[<?php echo $row['id']; ?>]" onclick="toggleItems(this.id)">
              </td>
              <?php
              }
              }
              ?>
              <?php
              $select_menu_items = mysqli_query($connection, $query); // read from top again
              while ($row = mysqli_fetch_assoc($select_menu_items)) {
              if ($row['vip'] === '0') { ?>
            <tr>
              <td><?php echo "<h5 class='text-danger'>&#10008;</h5>"; ?></td>
              <td id="family[<?php echo $row['id']; ?>]"><?php echo convertNumbers($row['family'], true); ?></td>
              <td><?php echo $row['last_name']; ?></td>
              <td><?php echo $row['first_name']; ?></td>
              <td><?php echo convertNumbers($row['id'], true); ?>
              <td><?php echo convertNumbers($row['remaining'], true); ?>
              <td><input class="form-control" type="text" name="donate_count[<?php echo $row['id']; ?>]"
                         id="donate_count[<?php echo $row['id']; ?>]" value="<?php
                echo convertNumbers(0, true);
                ?>" onkeypress="validate(event);" onkeyup="changeFieldsOnInput();" onclick="this.select();"
                         onpaste="return false;" disabled>
              </td>
              <td colspan="2"><input class="mx-auto" type="checkbox" name="donate_check[<?php echo $row['id']; ?>]"
                                     id="donate_check[<?php echo $row['id']; ?>]" onclick="toggleItems(this.id)"
                >
              </td>
              <?php
              }
              }
              ?>
          </table>
          <input class="form-control btn btn-primary" type="submit" name="execute-donation" value="ثبت">
        </form>

      </div>
    </div>
  </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="ptEdit.js"></script>
<script src="top.js"></script>
</body>
</html>
<script>
    var detailsLength = document.getElementsByClassName("details").length;
    var item = document.getElementsByClassName("details");
    for (var i = 0; i < detailsLength; i++)
        item.item(i).innerHTML = item.item(i).innerHTML.toPersianDigit();

    document.getElementById("t_bread").value = "0".toPersianDigit();
    document.getElementById("a-tbread").value = "0".toPersianDigit();
    document.getElementById("extra").value = "0".toPersianDigit();
    document.getElementById("a-extra").value = "0".toPersianDigit();

    String.prototype.toEnglishDigit = function () {
        var find = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        var replace = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        var replaceString = this;
        var regex;
        for (var i = 0; i < find.length; i++) {
            regex = new RegExp(find[i], "g");
            replaceString = replaceString.replace(regex, replace[i]);
        }
        return replaceString;
    };

    function validate(event) {
        let theEvent = event || window.event;

        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        let regex = /[0-9۰-۹]/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) {
                theEvent.preventDefault();
            }
        }
    }

    function refreshFields() {
        if (document.getElementById("mcd").checked) {
            fillFields();
        } else if (document.getElementById("acd").checked) {
            fillFieldsCount();
        }
    }

    function calculateBreadAmount(money) {
        return parseInt(parseInt(money, 10) / <?php echo getBreadPrice(); ?>, 10);
    }

    function calculateRemainingMoney() {
        let rem = document.getElementById("extra").innerText.toString().toEnglishDigit();
        rem = rem * <?php echo getBreadPrice(); ?>;
        document.getElementById("r_money").innerText = rem.toPersianDigit();
        document.getElementById("r_money").value = rem.toPersianDigit();
        document.getElementById("a-rmoney").value = rem.toPersianDigit();
    }

    function distributeBread() {
        let id = 5050505050;
        let check, input, family, flag = true, nAllMembers = countAllMembers();
        let fml = 0, nAllBreads = 0, quotaForEachPerson;
        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            check = document.getElementById("donate_check[" + (id + i) + "]");
            if (check.checked) {
                flag = false;
                family = document.getElementById("family[" + (id + i) + "]");
                fml = family.innerText.toEnglishDigit();
                fml = parseInt(fml, 10);
                quotaForEachPerson = sbc * fml / nAllMembers;
                quotaForEachPerson = parseInt(quotaForEachPerson, 10);
                nAllBreads = nAllBreads + quotaForEachPerson;
                input = document.getElementById("donate_count[" + (id + i) + "]");
                input.value = quotaForEachPerson.toString().toPersianDigit();
            }
        }
    }

    function toggleVipChecks() {
        var counter = 5050505050;
        var status = document.getElementById('select_all_vip').checked;

        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            var select_all_count = "donate_count[" + (counter + i) + "]";
            var select_vip_check = "donate_check[" + (counter + i) + "]";
            var select_vip = "vip[" + (counter + i) + "]";
            var inputs = document.getElementById(select_all_count);
            var checks = document.getElementById(select_vip_check);
            var vip = document.getElementById(select_vip);
            if (vip !== null) {
                inputs.value = "۰";
                inputs.disabled = !status;
                checks.checked = status;
            }
        }

        // console.log("toggleVIP: " + document.getElementById("a-tbread").value);
        refreshFields();
    }

    function countAllMembers() {
        let id = 5050505050;
        let check, family, nAllMembers = 0, fml = 0;
        for (let i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            check = document.getElementById("donate_check[" + (id + i) + "]");
            if (check.checked) {
                family = document.getElementById("family[" + (id + i) + "]");
                fml = family.innerText.toEnglishDigit();
                fml = parseInt(fml, 10);
                nAllMembers += fml;
            }
        }

        return nAllMembers;
    }

    function toggleAllChecks() {
        var counter = 5050505050;
        var status = document.getElementById('select_all').checked;
        document.getElementById('select_all_vip').checked = status;
        toggleVipChecks();

        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            var select_all_count = "donate_count[" + (counter + i) + "]";
            var select_all_check = "donate_check[" + (counter + i) + "]";
            var inputs = document.getElementById(select_all_count);
            var checks = document.getElementById(select_all_check);
            inputs.value = "۰";
            inputs.disabled = !status;
            checks.checked = status;
        }

        // console.log("toggleAll: " + document.getElementById("a-tbread").value);
        refreshFields();
    }

    function fillFields() {
        let val = document.getElementById("money_custom_donation").value;
        let sbc, bankUse, rem = 0;
        if (val.toEnglishDigit() !== "") {
            if (document.getElementById("bank_usage").value) {
                bankUse = document.getElementById("bank_usage").value.toString().toEnglishDigit();
                bankUse = parseInt(bankUse, 10);
            } else bankUse = 0;
            sbc = calculateBreadAmount(val.toString().toEnglishDigit());
            sbc += bankUse;
            //rem = parseInt(val.toString().toEnglishDigit(), 10) - sbc * <?php //echo getBreadPrice(); ?>//;
        } else {
            if (document.getElementById("bank_usage").value) {
                bankUse = document.getElementById("bank_usage").value.toString().toEnglishDigit();
                bankUse = parseInt(bankUse, 10);
            } else bankUse = 0;
            sbc = bankUse;
        }

        document.getElementById("t_bread").innerText = sbc.toString().toPersianDigit();
        document.getElementById("t_bread").value = sbc.toString().toPersianDigit();
        document.getElementById("a-tbread").value = sbc.toString().toPersianDigit();

        // document.getElementById("r_money").innerText = document.getElementById("money_custom_donation").value;
        // document.getElementById("r_money").innerText = rem.toString().toPersianDigit();
        // document.getElementById("r_money").value = rem;
        // document.getElementById("a-rmoney").value = rem;
        // document.getElementById("a-rmoney").value = document.getElementById("money_custom_donation").value;

        let id = 5050505050;
        let check, input, family, extra, flag = true, nAllMembers = countAllMembers();
        let fml = 0, nAllBreads = 0, quotaForEachPerson;

        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            check = document.getElementById("donate_check[" + (id + i) + "]");
            if (check.checked) {
                flag = false;
                family = document.getElementById("family[" + (id + i) + "]");
                fml = family.innerText.toEnglishDigit();
                fml = parseInt(fml, 10);
                quotaForEachPerson = sbc * fml / nAllMembers;
                quotaForEachPerson = parseInt(quotaForEachPerson, 10);
                nAllBreads = nAllBreads + quotaForEachPerson;
                input = document.getElementById("donate_count[" + (id + i) + "]");
                input.value = quotaForEachPerson.toString().toPersianDigit();
            }
        }
        extra = sbc - nAllBreads;
        extra = parseInt(extra, 10);

        if (flag) {
            extra = sbc - nAllBreads;
            extra = parseInt(extra, 10);
            //var rmoney = extra * <?php //echo getBreadPrice(); ?>// +document.getElementById("r_money").value;
            document.getElementById("extra").innerText = extra.toString().toPersianDigit();
            // document.getElementById("r_money").innerText = rmoney.toString().toPersianDigit();
            document.getElementById("a-extra").value = extra.toString().toPersianDigit();
            // document.getElementById("a-extra").innerText = extra.toString().toPersianDigit();
            document.getElementById("extra").value = extra.toString().toPersianDigit();
        } else if (extra !== undefined) {
            //var rmoney = extra * <?php //echo getBreadPrice(); ?>// +document.getElementById("r_money").value;
            document.getElementById("extra").innerText = extra.toString().toPersianDigit();
            // document.getElementById("r_money").innerText = rmoney.toString().toPersianDigit();
            document.getElementById("a-extra").value = extra.toString().toPersianDigit();
            // document.getElementById("a-extra").innerText = extra.toString().toPersianDigit();
            document.getElementById("extra").value = extra.toString().toPersianDigit();
        }
    }

    function fillFieldsCount() {
        var val = document.getElementById("amount_custom_donation").value;
        var sbc, bankUse;
        if (val !== "") {
            if (document.getElementById("bank_usage").value) {
                bankUse = document.getElementById("bank_usage").value.toString().toEnglishDigit();
                bankUse = parseInt(bankUse, 10);
            } else bankUse = 0;
            val = val.toEnglishDigit();
            val = parseInt(val, 10);
            sbc = val + bankUse;
        } else {
            if (document.getElementById("bank_usage").value) {
                bankUse = document.getElementById("bank_usage").value.toString().toEnglishDigit();
                bankUse = parseInt(bankUse, 10);
            } else bankUse = 0;
            sbc = bankUse;
        }

        document.getElementById("t_bread").innerText = sbc.toString().toPersianDigit();
        document.getElementById("t_bread").value = sbc.toString().toPersianDigit();
        document.getElementById("a-tbread").value = sbc.toString().toPersianDigit();

        let id = 5050505050;
        let check, input, family, extra, flag = true, nAllMembers = countAllMembers();
        let fml = 0, nAllBreads = 0, quotaForEachPerson;
        sbc = parseInt(sbc, 10);
        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            check = document.getElementById("donate_check[" + (id + i) + "]");
            if (check.checked) {
                flag = false;
                family = document.getElementById("family[" + (id + i) + "]");
                fml = family.innerText.toEnglishDigit();
                fml = parseInt(fml, 10);
                quotaForEachPerson = sbc * fml / nAllMembers;
                quotaForEachPerson = parseInt(quotaForEachPerson, 10);
                nAllBreads = nAllBreads + quotaForEachPerson;
                input = document.getElementById("donate_count[" + (id + i) + "]");
                input.value = quotaForEachPerson.toString().toPersianDigit();
            }
        }

        extra = sbc - nAllBreads;
        extra = parseInt(extra, 10);
        if (flag) {
            extra = sbc - nAllBreads;
            extra = parseInt(extra, 10);
            //var rmoney = extra * <?php //echo getBreadPrice(); ?>// +document.getElementById("r_money").value;
            document.getElementById("extra").innerText = extra.toString().toPersianDigit();
            // document.getElementById("r_money").innerText = rmoney.toString().toPersianDigit();
            document.getElementById("a-extra").value = extra.toString().toPersianDigit();
            // document.getElementById("a-extra").innerText = extra.toString().toPersianDigit();
            document.getElementById("extra").value = extra.toString().toPersianDigit();
        } else if (extra !== undefined) {
            //var rmoney = extra * <?php //echo getBreadPrice(); ?>// +document.getElementById("r_money").value;
            document.getElementById("extra").innerText = extra.toString().toPersianDigit();
            // document.getElementById("r_money").innerText = rmoney.toString().toPersianDigit();
            document.getElementById("a-extra").value = extra.toString().toPersianDigit();
            // document.getElementById("a-extra").innerText = extra.toString().toPersianDigit();
            document.getElementById("extra").value = extra.toString().toPersianDigit();
        }

    }

    function changeFieldsOnInput() {
        let id = 5050505050;
        let strBread, sbc;
        sbc = document.getElementById("a-tbread").value.toEnglishDigit();

        let check, extra, input, totalBread = 0;
        for (var i = 0; i < <?php getAllActiveFamiliesCount();?>; i++) {
            check = document.getElementById("donate_check[" + (id + i) + "]");
            if (check.checked) {
                input = document.getElementById("donate_count[" + (id + i) + "]");
                if (input.value !== "") {
                    var inputValue = input.value.toString().toEnglishDigit();
                    inputValue = parseInt(inputValue, 10);
                    totalBread += inputValue;
                }
            }
        }
        extra = sbc - totalBread;
        document.getElementById("extra").value = extra.toString().toPersianDigit();
        document.getElementById("extra").innerText = extra.toString().toPersianDigit();
        document.getElementById("a-extra").value = extra.toString().toPersianDigit();
    }

    function toggleItems(dc) {
        var id = dc.substring(13, 23);
        var donate_count_str = "donate_count[" + id + "]";
        var status = document.getElementById(donate_count_str).disabled;
        document.getElementById(donate_count_str).value = "۰";
        document.getElementById(donate_count_str).disabled = !status;

        // console.log("toggleItem: " + document.getElementById("a-tbread").value);
        refreshFields();
    }

    function showInputField() {
        var mcdStatus = document.getElementById("money_custom_donation").hidden;
        var acdStatus = document.getElementById("amount_custom_donation").hidden;

        if (!mcdStatus) {
            document.getElementById("money_custom_donation").hidden = !mcdStatus;
            document.getElementById("amount_custom_donation").value = "";
            document.getElementById("amount_custom_donation").hidden = !acdStatus;
        } else if (!acdStatus) {
            document.getElementById("amount_custom_donation").hidden = !acdStatus;
            document.getElementById("money_custom_donation").value = "";
            document.getElementById("money_custom_donation").hidden = !mcdStatus;
        }

        document.getElementById("select_all_vip").checked = false;
        document.getElementById("select_all").checked = false;

        var counter = 5050505050;
        for (var i = 0; i < <?php getAllActiveFamiliesCount(); ?>; i++) {
            var select_all_count = "donate_count[" + (counter + i) + "]";
            var select_all_check = "donate_check[" + (counter + i) + "]";
            var inputs = document.getElementById(select_all_count);
            var checks = document.getElementById(select_all_check);
            inputs.value = "۰";
            inputs.disabled = true;
            checks.checked = false;
        }
    }

    function showHint(str) {
        if (str.length === 0) {
            document.getElementById("txtHint").innerHTML = "";
            return;
        } else {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                }
            };
            xmlhttp.open("GET", "donate.php?q=" + str, true);
            xmlhttp.send();
        }
    }

    function useBank() {
        const usage = document.querySelector("#bank_usage");
        if (document.getElementById("use-bank").checked) {
            document.getElementById("bank_usage").disabled = false;
            document.getElementById("use-bank-all").disabled = false;
        } else {
            document.getElementById("bank_usage").disabled = true;
            document.getElementById("use-bank-all").disabled = true;
            document.getElementById("use-bank-all").checked = false;
            document.getElementById("bank_usage").value = "";
            if (usage.classList.contains("border"))
                usage.classList.remove("border");
            if (usage.classList.contains("border-danger"))
                usage.classList.remove("border-danger");
        }
    }

    function validateBankUsage(val) {
        let bank = document.getElementById("bank").value.toEnglishDigit();
        let bankUsage = document.getElementById("bank_usage").value.toEnglishDigit();
        const usage = document.querySelector("#bank_usage");
        bank = parseInt(bank, 10);
        bankUsage = parseInt(bankUsage, 10);

        let tb, rb;
        if (val !== "") {
            val = val.toEnglishDigit();
            val = parseInt(val, 10);
        } else {
            val = 0;
        }
        tb = document.getElementById("t_bread").value;
        rb = document.getElementById("extra").value;
        tb = parseInt(tb.toEnglishDigit(), 10) + val;
        rb = parseInt(rb.toEnglishDigit(), 10) + val;
        console.log("tb: " + tb);
        console.log("rb: " + rb);
        document.getElementById("t_bread").innerText = tb.toString().toPersianDigit();
        document.getElementById("a-tbread").value = tb.toString().toPersianDigit();

        document.getElementById("extra").innerText = rb.toString().toPersianDigit();
        document.getElementById("a-extra").value = rb.toString().toPersianDigit();

        if (bank < bankUsage) {
            document.getElementById("bank-msg").hidden = false;
            usage.classList.add("border");
            usage.classList.add("border-danger");
        } else {
            document.getElementById("bank-msg").hidden = true;
            usage.classList.remove("border-danger");
            usage.classList.remove("border");
        }

        // console.log("bankUse: " + document.getElementById("a-tbread").value);
        refreshFields();
    }

    function withdrawalAll() {
        const usage = document.querySelector("#bank_usage");
        if (document.getElementById("use-bank-all").checked) {
            document.getElementById("bank_usage").disabled = true;
            usage.classList.add("bg-success");
            document.getElementById("bank_usage").style.color = "#fff";
            document.getElementById("bank_usage").value = document.getElementById("bank").value;
        } else {
            document.getElementById("bank_usage").disabled = false;
            usage.classList.remove("bg-success");
            document.getElementById("bank_usage").style.color = "#000";
            document.getElementById("bank_usage").value = "";
        }
        validateBankUsage(document.getElementById("bank_usage").value);
    }
</script>
