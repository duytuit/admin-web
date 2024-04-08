function validate(form) {
  var select_list = $(form).find("select");
  var input_list = $(form).find("input");

  var err = $(form).find(".err");

  var error_count = 0;

  if (err.length > 0) {
    err.remove();
  }

  if (select_list.length > 0) {
    select_list.map(function (index, item) {
      if (!item.value) {
        $(
          `<p class="err" style="color: red">Trường này không được để trống</p>`
        ).insertAfter(item);
        error_count += 1;
      }
    });
  }

  if (input_list.length > 0) {
    input_list.map(function (index, item) {
      if (!item.value) {
        $(
          `<p class="err" style="color: red">Trường này không được để trống</p>`
        ).insertAfter(item);
        error_count += 1;
      }
    });
  }
  if (location.href.search("/promotion_manager") != -1) {
    error_count += check_promotion();
  }
  return error_count;
}

function check_promotion() {
  var err = 0;

  var discount = $("#promotion-discount")[0].value.replaceAll(".", "");
  console.log(discount);
  var type_discount = $("#type_discount")[0].value;

  if (discount.match(/[^$,.\d]/)) {
    $(
      `<p class="err" style="color: red">Giá trị khuyến mãi phải là số</p>`
    ).insertAfter($("#promotion-discount-main"));
    err += 1;
  }

  if (type_discount == 0) {
    if (discount > 100 || discount < 0) {
      $(
        `<p class="err" style="color: red">Giá trị khuyến mãi % không thể lớn hơn 100 hoặc nhỏ hơn 0</p>`
      ).insertAfter($("#promotion-discount-main"));
      err += 1;
    }
  } else {
    if (discount < 1000) {
      $(
        `<p class="err" style="color: red">Giá trị khuyến mãi VND không thể nhỏ hơn 1000 </p>`
      ).insertAfter($("#promotion-discount-main"));
      err += 1;
    }
  }

  var begin = $("#time_use_from")[0].value;
  var end = $("#time_use_to")[0].value;

  if (new Date(begin) > new Date(end)) {
    $(
      `<p class="err" style="color: red">Ngày kết thúc không đúng </p>`
    ).insertAfter($("#time_use_to"));
    err += 1;
  }

  return err;
}
