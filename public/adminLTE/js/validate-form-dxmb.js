$(function(){
    $("#receipt_form").validate({
		rules: {
			"customer_fullname": {
				required: true
			},
			"customer_address": {
				required: true
			},
			"customer_paid_string": {
				required: true
            }
        },
        messages: {
            "customer_fullname": {
				required: "Tên người nộp không được để trống."
			},
			"customer_address": {
				required: "Đia chỉ người nộp không được để trống."
			},
			"customer_paid_string": {
				required: "Số tiền người nộp phải lớn hơn 0"
            }
        }
    });
    
    $("#create_info").validate({
		rules: {
			"cycle_name": {
				required: true
			},
			"apartment_name": {
				required: true
			},
			"customer_address": {
				required: true
            },
            "from_date_previous": {
				required: true
            },
            "to_date_previous": {
				required: true
            },
            "sumery": {
				required: true
			}
        },
        messages: {
            "cycle_name": {
				required: "Kỳ tháng không được để trống."
			},
			"apartment_name": {
				required: "Căn hộ không được để trống."
			},
			"customer_address": {
				required: "Địa chỉ không được để trống."
            },
            "from_date_previous": {
				required: "Từ ngày không được để trống."
            },
            "to_date_previous": {
				required: "Đến ngày không được để trống."
            },
            "sumery": {
				required: "Tiền phát sinh không được để trống."
			}
        }
    });
});