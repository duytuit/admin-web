<?php

namespace App\Commons;

use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Promotion\Promotion;
use App\Models\Service\Service;
use Illuminate\Support\Facades\Redis;
use App\Repositories\Building\CompanyRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class Helper
{
    const carriers_number = [
        '096' => 'Viettel',
        '097' => 'Viettel',
        '098' => 'Viettel',
        '032' => 'Viettel',
        '033' => 'Viettel',
        '034' => 'Viettel',
        '035' => 'Viettel',
        '036' => 'Viettel',
        '037' => 'Viettel',
        '038' => 'Viettel',
        '039' => 'Viettel',
     
        '090' => 'Mobifone',
        '093' => 'Mobifone',
        '070' => 'Mobifone',
        '071' => 'Mobifone',
        '072' => 'Mobifone',
        '076' => 'Mobifone',
        '078' => 'Mobifone',
     
        '091' => 'Vinaphone',
        '094' => 'Vinaphone',
        '083' => 'Vinaphone',
        '084' => 'Vinaphone',
        '085' => 'Vinaphone',
        '087' => 'Vinaphone',
        '089' => 'Vinaphone',
     
        '099' => 'Gmobile',
     
        '092' => 'Vietnamobile',
        '056' => 'Vietnamobile',
        '058' => 'Vietnamobile',
     
        '095'  => 'SFone'

     ];
    public static function detect_number($number) { // false : không phải số điện thoại
        $number = str_replace(array('-', '.', ' '), '', $number);
    
        // $number is not a phone number
        if (!preg_match('/(0)[0-9]{9}/', $number) || strlen((string)$number) !==10){
            return false;
        } else{
            return true;
        }
    }
     /**
     * allow FILE upload mime types
     */
    const FILE_MIME_TYPES = [
        'jpeg',
        'jpg',
        'gif',
        'png',
        'svg',
        'webp',
        'doc',
        'docx',
        'csv',
        'rtf',
        'xlsx',
        'xls',
        'pdf',
        'txt',
        'zip'
    ];
    const status_task = [
        0 => "Đã hủy",
        1 => "Đã hoàn thành",
        3 => "Chưa thực hiện",
        5 => "Đang làm",
        6 => "Chờ giám sát duyệt",
    ];
    const status_history_task = [
        "CREATE" => 'Tạo mới',
        "UPDATE" => 'Cập nhật',
        "DELETE" => 'Xóa',
        "CHANGE_STATUS" => 'Đổi trạng thái',
        "CHANGE_ASSIGNED" => 'Đổi người thực hiện',
        "CHANGE_ASSIGNED_MONITOR" => 'Đổi người giám sát',
        "NOT_ACCEPT_SHIFTS" => 'Không xác nhận đổi ca',
        "ACCEPT_SHIFTS" => 'Xác nhận đổi ca',
        "SEND_SHIFTS" => 'Gửi xác nhận đổi ca',
    ];
    const status_task_html = [
        0 => '<span class="label labela-success" style="background-color:#EB5A3D">Đã hủy</span>',
        1 => '<span class="label labela-success" style="background-color:#85B372">Đã hoàn thành</span>',
        3 => '<span class="label labela-success" style="background-color:#A4A4A4">Chưa thực hiện</span>',
//        4 => '<span class="label labela-success" style="background-color:#9788C3">Quá hạn</span>',
        5 => '<span class="label labela-success" style="background-color:#104aec">Đang làm</span>',
        6 => '<span class="label labela-success" style="background-color:#D94581">Chờ giám sát duyệt</span>'
    ];
    const loai_danh_muc = [    
         0 => "Phí khác",
         2 => "Phí dịch vụ",
         3 => "Phí nước",
         4 => "Phí phương tiện",
         5 => "Phí điện",
        'phieu_ke_toan' => "Phiếu Kế Toán",
        'chuyen_khoan_ve_cdt'=> "Chuyển khoản về CĐT",
        'chuyen_khoan' => "Chuyển khoản",
        'chuyen_khoan_auto' => "Chuyển khoản Auto",
        'phieu_bao_co' => "Phiếu báo có",
        'phieu_thu_truoc' => "Phiếu thu khác",
        'phieu_chi_khac' => "Phiếu chi khác",
        'phieu_thu_ky_quy' => "Phiếu thu ký quỹ",
        'phieu_hoan_ky_quy' => "Phiếu hoàn ký quỹ",
        'phieu_dieu_chinh' => "Phiếu điều chỉnh",
        'tien_mat' => "Tiền mặt",
        'phieu_thu' => "Phiếu thu",
        'PHIEU_THU' => "Phiếu thu",
        'can_tru_cdt' => "Cấn trừ CĐT",
        'tien_ve_cdt' => "Tiền về CĐT",
        'can_tru_tkdx' => "Cấn trừ TKDX",
        'cdt_mien_giam' => "CĐT miễn giảm",
        'vi' => "Ví",
        'vi_banking' => "Ví",
        'khac' => "Khác",
        'phieu_chi' => "Phiếu chi",
        'visual_account' => "Chuyển khoản",
        'ATM_CARD' => "Chuyển khoản",
        'vnpay' => "VNpay",
        'viet_qr' => "VietQR",
        '9Pay' => "9Pay",
        'Vi-QR' => "VietQR",
        'atm_card' => "ATM CARD",
        'credit_card' => "Credit CARD",
        'cdt_can_tru_chong_tham'=> "chan tru chong tham"
    ];
    const loai_phi_dich_vu = [    
        0 => "Phí khác",
        2 => "Phí dịch vụ",
        3 => "Phí nước",
        4 => "Phí phương tiện",
        5 => "Phí điện",
        6 => "Phí nước nóng",
        7 => "Tiện ích",
   ];
    const service_type = [
        1 => "GYM",
        2 => "Bể bơi",
        3 => "Sauna",
    ];
    const electric_type = [
        0 => "Phí điện",
        1 => "Phí nước",
        2 => "Phí nước nóng",
    ];
   const type_sender = [    
        0 => "Hóa đơn",
        1 => "Ý kiến",
        2 => "Phiếu thu",
        3 => "bản tin",
        4 => "cộng đồng",
        6 => "tin hay",
        7 => "sự kiện",
        8 => "đăng ký dich vụ",
        11 => "tài chính",
    ];
   const status_user_request = [    
    0 => "Chờ xử lý",
    1 => "BQL đang xử lý",
    2 => "Chờ cư dân phản hồi",
    3 => "Thành công",
    4 => "Hủy",
   ];
   const type_user_request = [    
    1 => "Thêm phương tiện",
    2 => "Hủy phương tiện",
    3 => "Cấp lại thẻ xe",
    4 => "Chuyển đồ",
    5 => "Sửa chữa",
    6 => "Tiện ích"
   ];
   const method_payment = [    
    1 => "Chuyển khoản ngân hàng qua tài khoản ảo 9pay",
    2 => "Chuyển khoản qua tài khoản ngân hàng nội địa",
    3 => "Chuyển khoản qua tài khoản VISA,MASTER",
    4 => "Chuyển khoản VIET_QR",
   ];
   const trang_thai = [    
        0 => "Chờ phản hồi",
        1 => "Hoàn thành",
        2 => "Đã tiếp nhận"
    ];
    const vote = [    
        1 => "Rất không hài lòng",
        2 => "Chưa hài lòng",
        3 => "Bình thường",
        4 => "Hài lòng",
        5 => "Rất hài lòng"
    ];
    const action = [    
        'insert' => "Thêm mới",
        'view' => "Xem",
        'update' => "Cập nhật",
        'delete' => "Xóa",
        'import' => "Import",
        'export' => "Export",
    ];
    const request_task = [
        0 => '<span class="label labela-success" style="background-color:#85B372">chờ xác nhận</span>',
        1 => '<span class="label labela-success" style="background-color:#A4A4A4">chấp nhận</span>',
        2 => '<span class="label labela-success" style="background-color:#9788C3">không chấp nhận</span>',
    ];
    const priority_task = [
        1 => '<span class="label labela-success" style="background-color:#9788C3">Thấp</span>',
        2 => '<span class="label labela-success" style="background-color:#A4A4A4">Bình thường</span>',
        3 => '<span class="label labela-success" style="background-color:#85B372">Cao</span>',
        4 => '<span class="label labela-success" style="background-color:#EB5A3D">Gấp</span>'
    ];
    const loai_phieu_thu = [  
        'phieu_thu' => "Tiền mặt",  
        'chuyen_khoan' => "Chuyển khoản",
        'chuyen_khoan_auto' => "Chuyển khoản Auto",
        'phieu_bao_co' => "Phiếu báo có",
        'visual_account' => "Chuyển khoản",
        'ATM_CARD' => "Chuyển khoản",
        'can_tru_cdt' => "Cấn trừ CĐT",
        'can_tru_tkdx' => "Cấn trừ TKDX",
        'cdt_mien_giam' => "CĐT miễn giảm",
        'visual_account' => "Chuyển khoản",
        'ATM_CARD' => "Chuyển khoản",
        'vi' => "Ví",
        'khac' => "Khác",
        'viet_qr' => "Viet QR",
        'atm_card' => "ATM CARD",
        'credit_card' => "Credit CARD",
        'chuyen_khoan_ve_cdt'=> "Chuyển khoản về CĐT"
        
    ];
    const type_import = [  
        0 => "Import Cư dân",
        2 => "Import giao dịch vietQR",
    ];

    const type_utilities = [  
        1 => "Thuê phòng cầu lông",
        2 => "Phòng họp",
        3 => "Phòng bóng chuyền",
        4 => "Thuê sân bóng đá",
        5 => "Thuê bàn nướng BQQ",
    ];
    const app_company = [  
        0 => "App Khác",
        1 => "App Asahi",
        2 => "App Building Care",
    ];

    const type_receipt = [  
        [
            "text" => "phieu_thu",
            "value" => "Phiếu thu"
        ],
        [
            "text" => "phieu_bao_co",
            "value" => "Phiếu báo có"
        ],
        [
            "text" => "phieu_thu_truoc",
            "value" => "Phiếu thu khác"
        ],
        [
            "text" => "phieu_chi",
            "value" => "Phiếu chi"
        ],
        [
            "text" => "phieu_chi_khac",
            "value" => "Phiếu chi khác"
        ],
        [
            "text" => "phieu_ke_toan",
            "value" => "Phiếu kế toán"
        ],
        [
            "text" => "phieu_dieu_chinh",
            "value" => "Phiếu điều chỉnh"
        ]
    ];

    const tai_khoan_ke_toan_toa_nha = [    
        131300 =>	'Phải thu nghiệm thu CĐT',
        131400 =>	'Phải thu khác',
        131700 =>	'Phải thu phí dịch vụ ',
        131800 =>	'Phải thu phí nước, điện',
        131900 =>	'Phải thu phí xe',
        338860 =>	'Phải trả khoản thu hộ ',
        338880 =>	'Phải trả khoản thu cọc, phải trả khác',
        112100 =>	'Tiền ngân hàng',
        111100 =>	'Tiền mặt ',
        113100 =>	'Tiền đang chuyển'
    ];

    const config_menu_v1 = [6,12,2]; // menu kế toán , công nợ v1

    const config_menu_v2 = [18,19]; // menu kế toán , công nợ v2

    const form_register_service = [
        1 => [
            'title' => 'Phiếu đăng ký cấp thẻ thang máy',
            'param' => [
                '@ten_khach_hang',
                '@can_ho',
                '@email',
                '@phone',
                '@ngay_de_nghi'
            ],
        ],
        2 => [
            'title' => 'Phiếu đăng ký hủy thẻ thang máy',
            'param' => [
                '@ngay_de_nghi'
            ],
        ],
        4 =>  [
            'title' => 'Phiếu đăng ký chuyển đồ',
            'param' => [
                '@ten_khach_hang',
                '@email',
                '@can_ho',
                '@dien_thoai_lien_he',
                '@ngay_van_chuyen',
                '@du_lieu'
            ],
        ],
        5 =>  [
            'title' => 'Phiếu đăng ký sửa chữa',
            'param' => [
                '@ten_khach_hang',
                '@sdt_kh',
                '@can_ho',
                '@ten_nha_thau',
                '@nguoi_chiu_trach_nhiem',
                '@sdt_nha_thau',
                '@start_time',
                '@end_time',
                '@ngay_bat_dau',
                '@ngay_ket_thuc',
            ],
        ], 
        6 =>  [
            'title' => 'Phiếu đăng ký tiện ích',
            'param' => [
                '@ten_khach_hang',
                '@can_ho',
                '@sdt',
                '@ngay_de_nghi',
                '@du_lieu'
            ],
        ]
    ];

    const config_receipt = [
        [
            "title" => "Thu khác",
            "value" => "PTT_K",
            "key" => "provisional_receipt",
            "note" => "<p>Danh mục Phiếu thu khác</p>"
        ],
        [
            "title" => "Chi nộp tiền về công ty",
            "value" => "PC_VCT",
            "key" => "receipt_payment_slip",
            "note" => "<p>Danh mục phiếu chi khác</p>"
        ],
        [
            "title" => "Phiếu điều chỉnh",
            "value" => "PĐC_KP",
            "key" => "adjustment_slip",
            "note" => "<p>Phiếu điều chỉnh</p>"
        ],
        [
            "title" => "Ký quỹ sửa chữa",
            "value" => "KQSC",
            "key" => "receipt_deposit",
            "note" => "<p>receipt_deposit</p>"
        ],
        [
            "title" => "Hoàn quỹ sửa chữa",
            "value" => "HQKP",
            "key" => "receipt_payment_deposit",
            "note" => "<p>receipt_payment_deposit</p>"
        ],
        [
            "title" => "Hoàn cọc vé xe",
            "value" => "HQKP",
            "key" => "receipt_payment_deposit",
            "note" => "<p>receipt_payment_deposit</p>"
        ],
        [
            "title" => "Cọc vé xe",
            "value" => "KQKP",
            "key" => "receipt_deposit",
            "note" => "<p>receipt_deposit</p>"
        ],
        [
            "title" => "Mã phiếu báo có",
            "value" => "BC-K-PARK",
            "key" => "credit_transfer_receipt_code",
            "note" => "<p>Mã phiếu báo có</p>"
        ],
        [
            "title" => "Mã Phiếu thu khác",
            "value" => "PTT-K-PARK",
            "key" => "provisional_receipt_code",
            "note" => "<p>Mã Phiếu thu khác</p>"
        ],
        [
            "title" => "Mã phiếu kế toán",
            "value" => "PKT-K-PARK",
            "key" => "accounting_receipt_code",
            "note" => "<p>Mã phiếu kế toán</p>"
        ],
        [
            "title" => "Mã bảng kê",
            "value" => "BKKP",
            "key" => "bill_code",
            "note" => "<p>Mã bảng kê</p>"
        ],
        [
            "title" => "Mã Phiếu thu",
            "value" => "PTKP",
            "key" => "receipt_code",
            "note" => "<p>Mã Phiếu thu</p>"
        ],
        [
            "title" => "Mã phiếu chi khác",
            "value" => "PCK",
            "key" => "receipt_payment_slip_code_other",
            "note" => "<p>Mã phiếu chi khác</p>"
        ],
        [
            "title" => "Mã phiếu chi",
            "value" => "PC",
            "key" => "receipt_payment_slip_code",
            "note" => "<p>Mã phiếu chi</p>"
        ]
    ];
    
    public static function checkAdmin($id)
    {
      $adminId =[1,11,7445, 7605,8092.13027];

      return in_array($id, $adminId);
    }
    public static function checkAsahi($id)
    {
      $BuildingId =[68];

      return in_array($id, $BuildingId);
    }

    public static function getCustomerCode($pub_user_id, $building_active_id)
    {
        $company = app(CompanyRepository::class)->getPrefixCompanyCode($building_active_id);
        if (!$company) {
            throw new \Exception("Không tìm thấy Công ty thích hợp.(0309)", 0);

        }

        $lastId = app(PublicUsersProfileRespository::class)->getLastIdWithPrefix($pub_user_id, $company->customer_code_prefix, $company->id);

        return [
            'customer_code_prefix'=> $company->customer_code_prefix,
            'customer_code' =>$lastId
        ];
    }
    public static function convert_vi_to_en($str) {
      $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
      $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
      $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
      $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
      $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
      $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
      $str = preg_replace("/(đ)/", "d", $str);
      $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
      $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
      $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
      $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
      $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
      $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
      $str = preg_replace("/(Đ)/", "D", $str);
      //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
      return $str;
    }
    public static function banks()
    {
      $banks = [
                  'ABBANK',
                  'ACB',
                  'AGRIBANK',
                  'BACABANK',
                  'BIDV',
                  'DONGABANK',
                  'EXIMBANK',
                  'HDBANK',
                  'IVB',
                  'MBBANK',
                  'MSBANK',
                  'NAMABANK',
                  'NCB',
                  'OCB',
                  'OJB',
                  'PVCOMBANK',
                  'SACOMBANK',
                  'SAIGONBANK',
                  'SCB',
                  'SHB',
                  'TECHCOMBANK',
                  'TPBANK',
                  'VPBANK',
                  'SEABANK',
                  'VIB',
                  'VIETABANK',
                  'VIETBANK',
                  'VIETCAPITALBANK',
                  'VIETCOMBANK',
                  'VIETINBANK',
                  'BIDC',
                  'LAOVIETBANK',
                  'WOORIBANK',
                  'AMEX',
                  'VISA',
                  'MASTERCARD',
                  'JCB',
                  'UPI',
                  'VNMART',
                  'VNPAYQR',
                  '1PAY',
                  'FOXPAY',
                  'VIMASS',
                  'VINID',
                  'VIVIET',
                  'VNPTPAY',
                  'YOLO',
                  'VIETCAPITALBANK',
      ];

      return $banks;
    }
    public static function template_emails(){
        $template_emails = [
           'bdc',
           'asahi'
        ];
         return $template_emails;
    }
     public static function status_worktask(){
        $status_worktask=[
          [
              "text" => "not_yet_started",
              "value" => "Chưa thực hiện"
          ],
          [
              "text" => "processing",
              "value" => "Đang thực hiện"
          ],
          [
              "text" => "started",
              "value" => "Đã thực hiện"
          ],
          [
              "text" => "pending",
              "value" => "Chờ phản hồi"
          ],
          [
              "text" => "return",
              "value" => "Trả về"
          ],
          [
              "text" => "switch_request",
              "value" => "YC chuyển ca"
          ],
          [
              "text" => "deny_request",
              "value" => "Duyệt giám sát"
          ],
          [
              "text" => "done",
              "value" => "Hoàn thành"
          ]
        ];
          return $status_worktask;
     }
    public static function genUuid($salt, $len = 8)
    {
        $hex = md5($salt . uniqid("", true));
        $pack = pack('H*', $hex);
        $tmp = base64_encode($pack);
        $uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);
        $len = max(4, min(128, $len));
        while (strlen($uid) < $len) {
            $uid .= self::genUuid(22);
        }

        return strtolower(substr($uid, 0, $len));
    }
    public static function getToken($user_id)
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_TOKEN' . $user_id);
    }
    public static function setToken($user_id, $token)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . '_DXMB_TOKEN' . $user_id, $token);
    }
    public static function delToken($user_id)
    {
        return Cache::store('redis')->forget(env('REDIS_PREFIX') . '_DXMB_TOKEN' . $user_id);
    }
    public static function getRole($user_id)
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_ROLE' . $user_id);
    }
    public static function setRole($user_id, $data_role)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . '_DXMB_ROLE' . $user_id, $data_role);
    }
    public static function delRole($user_id)
    {
        return Cache::store('redis')->forget(env('REDIS_PREFIX') . '_DXMB_ROLE' . $user_id);
    }

    public static function setMaintenance($maintenance = false)
    {
        return Cache::store('redis')->put( env('REDIS_PREFIX') . '_DXMB_BUILDING_MAINTENANCE',$maintenance);
    }

    public static function getMaintenance()
    {
        return Cache::store('redis')->get( env('REDIS_PREFIX') . '_DXMB_BUILDING_MAINTENANCE');
    }

    public static function list_apartment_handover(){
    return [
        [
            "text" => 1,
            "value" => "Chưa đủ điều kiện"
        ],
        [
            "text" => 2,
            "value" => "Đủ điều kiện"
        ],
        [
            "text" => 3,
            "value" => "Đã gửi thông báo"
        ],
        [
            "text" => 4,
            "value" => "Đã xác nhận"
        ],
        [
            "text" => 5,
            "value" => "Từ chối"
        ],
        [
            "text" => 6,
            "value" => "Đã bàn giao"
        ]
    ];
    }
    public static function status_asset_apartment(){
        return [
            [
                "text" => 1,
                "value" => "Đã bàn giao"
            ],
            [
                "text" => 2,
                "value" => "Chưa bàn giao"
            ],
            [
                "text" => 3,
                "value" => "Từ chối"
            ]
           ];
    }

    public static function getExtensionFileBase64($file_name)
    {
        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];

        $type_file = explode(".",$file_name);

        $type_file = $type_file[count($type_file) - 1];

        $extFile = "";

        foreach ($mime_map as $key => $value) {
            if($type_file==$value)
                $extFile =  "data:".$key.";base64,";
        }
        return $extFile;
    }

    static function check_file_type_is_image($file_path) {
        $type_file = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
       
        $FILE_MIME_TYPES = [
            'jpeg',
            'jpg',
            'png',
            'gif'
        ];
        if(in_array($type_file,$FILE_MIME_TYPES)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Upload file cơ bản từ form submit lên, truyền thông tin trường file vào hệ thống sẽ xử lý và upload lên cdn.dxmb.vn
     * @param string $fileName
     * @return mixed
     */
    static function doUploadSingle($request)
    {

        $file = $request->file('file');
        $folder = $request->get('folder');
        if(!$file){
            return response()->json("Bạn cần chọn file trước khi upload", 400);
        }

        $post_fields['file'] = new \CurlFile($file->path(), $file->getClientMimeType(), $file->getClientOriginalName());
        $curl_handle = curl_init('https://cdn.dxmb.vn/do-upload');
        $headers[]  = 'apiKey: EnvhsWCWNivCOqx8ASuIXQ9OAseKxQABuyL';
        if($folder){
            $headers[]  = 'folder: '.$folder;
        }
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_VERBOSE, 0);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        curl_setopt($curl_handle, CURLOPT_POST, true);
        @curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_fields);
        $returned_data = curl_exec($curl_handle);
        curl_close($curl_handle);
        $returned_data = json_decode($returned_data);
        return $returned_data;
    }
    static function doUpload($file, $fileName, $folder = null)
    {
        if (!$fileName) {
            return response()->json("Bạn cần chọn file trước khi upload", 400);
        }
        $headers['apiKey']  = 'EnvhsWCWNivCOqx8ASuIXQ9OAseKxQABuyL';
        if ($folder) {
            $headers['folder']  = $folder;
        }
        $client = new Client(['headers' => $headers]);
        $options = [
            'multipart' => [
                [
                    'Content-type' => 'multipart/form-data',
                    'name' => 'file',
                    'contents' => fopen($file, 'r'),
                    'filename' => $fileName,
                ]
            ],
        ];
        $response = $client->post('https://cdn.dxmb.vn/do-upload', $options);
        return  json_decode($response->getBody()->getContents());
    }
        /**
     * Hỗ trợ bóc tách file extension (từ file name hoặc string)....
     * @param $file_name
     * @return false|string
     */
    public static function getFileExtension($file_name)
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }
    /**
     * Convert string to slug : chuỗi của tôi => chuoi-cua-toi
     * @param $str
     * @param string $replace
     * @param false $extra
     * @return array|false|string|string[]|null
     */
    public static function convertToSlug($str, $replace = "-", $extra = false)
    {
        $str = self::removeAccent($str);
        if ($extra) {
            $str = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);
        }
        return self::url_slug($str, ['delimiter' => $replace]);
    }
    private static function url_slug($str, $options = [])
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        // $str = strtolower($str);
        $str      = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
        $defaults = [
            'delimiter'     => '-',
            'limit'         => NULL,
            'lowercase'     => TRUE,
            'replacements'  => [],
            'transliterate' => TRUE,
        ];

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = [
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z',
        ];

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);

        }
        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
    public static function removeAccent($mystring)
    {
        $marTViet = [
            // Chữ thường
            "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ",
            "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ",
            "ì", "í", "ị", "ỉ", "ĩ",
            "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ",
            "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
            "ỳ", "ý", "ỵ", "ỷ", "ỹ",
            "đ", "Đ", "'",
            // Chữ hoa
            "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
            "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
            "Ì", "Í", "Ị", "Ỉ", "Ĩ",
            "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
            "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
            "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
            "Đ", "Đ", "'", ".",
        ];
        $marKoDau = [
            /// Chữ thường
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
            "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
            "i", "i", "i", "i", "i",
            "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
            "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
            "y", "y", "y", "y", "y",
            "d", "D", "",
            //Chữ hoa
            "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A",
            "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
            "I", "I", "I", "I", "I",
            "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O",
            "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
            "Y", "Y", "Y", "Y", "Y",
            "D", "D", "", "-",
        ];
        return str_replace($marTViet, $marKoDau, $mystring);
    }
   public static function get_folder_image_cache($image, $size = 'big', $isFolder = false)
    {
        $hash = md5($image);
        $source = parse_url($image);
        $folder = '/uploads/' . $size . str_replace(['.png'], '.jpg', $source['path']);
        if ($isFolder) {
            return $folder;
        }
        return 'https://cdn.dxmb.vn'.$folder . '?s=' . base64_encode($image);
    }
    public static function link_image_item($item, $image = '', $type = 'blog')
    {
        if (isset($item->image) && !$item->image) {
            return asset('v2/banners/no-image.png');
        }
        return self::get_folder_image_cache($image);
        return $image;
    }

    public static function link_image_small($image = '')
    {

        if (!$image) {
            return asset('v2/banners/no-image.png');
        }
        return self::get_folder_image_cache($image, 'small');
    }

    public static function link_image_mini($image = '')
    {

        if (!$image) {
            return asset('v2/banners/no-image.png');
        }
        return self::get_folder_image_cache($image, 'mini');
    }
    public static function link_image_big($image = '')
    {
        if (!$image) {
            return asset('v2/banners/no-image.png');
        }
        return self::get_folder_image_cache($image, 'big');
    }
    public static function link_image_medium($image = '')
    {
        if (!$image) {
            return asset('v2/img/no-image.jpg');
        }
        return self::get_folder_image_cache($image, 'medium');
    }
    public static function list_bank_viqr(){
       return [
        ["id"=>17,"name"=>"Ngân hàng TMCP Công thương Việt Nam","code"=>"ICB","bin"=>"970415","isTransfer"=>1,"short_url"=>"VietinBank","logo"=>"https://api.viqr.net/icons/ICB.3d4d6760.png","support"=>3],
        ["id"=>43,"name"=>"Ngân hàng TMCP Ngoại Thương Việt Nam","code"=>"VCB","bin"=>"970436","isTransfer"=>1,"short_url"=>"Vietcombank","logo"=>"https://api.viqr.net/icons/VCB.237d4924.png","support"=>3],
        ["id"=>21,"name"=>"Ngân hàng TMCP Quân đội","code"=>"MB","bin"=>"970422","isTransfer"=>1,"short_url"=>"MBBank","logo"=>"https://api.viqr.net/icons/MB.f9740319.png","support"=>3],
        ["id"=>2,"name"=>"Ngân hàng TMCP Á Châu","code"=>"ACB","bin"=>"970416","isTransfer"=>1,"short_url"=>"ACB","logo"=>"https://api.viqr.net/icons/ACB.6e7fe025.png","support"=>3],
        ["id"=>47,"name"=>"Ngân hàng TMCP Việt Nam Thịnh Vượng","code"=>"VPB","bin"=>"970432","isTransfer"=>1,"short_url"=>"VPBank","logo"=>"https://api.viqr.net/icons/VPB.ca2e7350.png","support"=>3],
        ["id"=>39,"name"=>"Ngân hàng TMCP Tiên Phong","code"=>"TPB","bin"=>"970423","isTransfer"=>1,"short_url"=>"TPBank","logo"=>"https://api.viqr.net/icons/TPB.883b6135.png","support"=>3],
        ["id"=>22,"name"=>"Ngân hàng TMCP Hàng Hải","code"=>"MSB","bin"=>"970426","isTransfer"=>1,"short_url"=>"MSB","logo"=>"https://api.viqr.net/icons/MSB.1b076e2a.png","support"=>3],
        ["id"=>23,"name"=>"Ngân hàng TMCP Nam Á","code"=>"NAB","bin"=>"970428","isTransfer"=>1,"short_url"=>"NamABank","logo"=>"https://api.viqr.net/icons/NAB.f74b0fa8.png","support"=>3],
        ["id"=>20,"name"=>"Ngân hàng TMCP Bưu Điện Liên Việt","code"=>"LPB","bin"=>"970449","isTransfer"=>1,"short_url"=>"LienVietPostBank","logo"=>"https://api.viqr.net/icons/LPB.07a7c83b.png","support"=>3],
        ["id"=>44,"name"=>"Ngân hàng TMCP Bản Việt","code"=>"VCCB","bin"=>"970454","isTransfer"=>1,"short_url"=>"VietCapitalBank","logo"=>"https://api.viqr.net/icons/VCCB.654a3506.png","support"=>3],
        ["id"=>4,"name"=>"Ngân hàng TMCP Đầu tư và Phát triển Việt Nam","code"=>"BIDV","bin"=>"970418","isTransfer"=>1,"short_url"=>"BIDV","logo"=>"https://api.viqr.net/icons/BIDV.862fd58b.png","support"=>3],
        ["id"=>36,"name"=>"Ngân hàng TMCP Sài Gòn Thương Tín","code"=>"STB","bin"=>"970403","isTransfer"=>1,"short_url"=>"Sacombank","logo"=>"https://api.viqr.net/icons/STB.a03fef2c.png","support"=>3],
        ["id"=>45,"name"=>"Ngân hàng TMCP Quốc tế Việt Nam","code"=>"VIB","bin"=>"970441","isTransfer"=>1,"short_url"=>"VIB","logo"=>"https://api.viqr.net/icons/VIB.4ecb28e6.png","support"=>3],
        ["id"=>12,"name"=>"Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh","code"=>"HDB","bin"=>"970437","isTransfer"=>1,"short_url"=>"HDBank","logo"=>"https://api.viqr.net/icons/HDB.4256e826.png","support"=>3],
        ["id"=>33,"name"=>"Ngân hàng TMCP Đông Nam Á","code"=>"SEAB","bin"=>"970440","isTransfer"=>1,"short_url"=>"SeABank","logo"=>"https://api.viqr.net/icons/SEAB.1864a665.png","support"=>3],
        ["id"=>11,"name"=>"Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu","code"=>"GPB","bin"=>"970408","isTransfer"=>0,"short_url"=>"GPBank","logo"=>"https://api.viqr.net/icons/GPB.29bd127d.png","support"=>1],
        ["id"=>30,"name"=>"Ngân hàng TMCP Đại Chúng Việt Nam","code"=>"PVCB","bin"=>"970412","isTransfer"=>1,"short_url"=>"PVcomBank","logo"=>"https://api.viqr.net/icons/PVCB.6129f342.png","support"=>3],
        ["id"=>24,"name"=>"Ngân hàng TMCP Quốc Dân","code"=>"NCB","bin"=>"970419","isTransfer"=>1,"short_url"=>"NCB","logo"=>"https://api.viqr.net/icons/NCB.7d8af057.png","support"=>3],
        ["id"=>37,"name"=>"Ngân hàng TNHH MTV Shinhan Việt Nam","code"=>"SHBVN","bin"=>"970424","isTransfer"=>1,"short_url"=>"ShinhanBank","logo"=>"https://api.viqr.net/icons/SHBVN.b6c0e806.png","support"=>3],
        ["id"=>31,"name"=>"Ngân hàng TMCP Sài Gòn","code"=>"SCB","bin"=>"970429","isTransfer"=>1,"short_url"=>"SCB","logo"=>"https://api.viqr.net/icons/SCB.5ca4bec4.png","support"=>3],
        ["id"=>29,"name"=>"Ngân hàng TMCP Xăng dầu Petrolimex","code"=>"PGB","bin"=>"970430","isTransfer"=>1,"short_url"=>"PGBank","logo"=>"https://api.viqr.net/icons/PGB.825cbbda.png","support"=>3],
        ["id"=>42,"name"=>"Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam","code"=>"VBA","bin"=>"970405","isTransfer"=>0,"short_url"=>"Agribank","logo"=>"https://api.viqr.net/icons/VBA.d72a0e06.png","support"=>0],
        ["id"=>38,"name"=>"Ngân hàng TMCP Kỹ thương Việt Nam","code"=>"TCB","bin"=>"970407","isTransfer"=>1,"short_url"=>"Techcombank","logo"=>"https://api.viqr.net/icons/TCB.b2828982.png","support"=>3],
        ["id"=>34,"name"=>"Ngân hàng TMCP Sài Gòn Công Thương","code"=>"SGICB","bin"=>"970400","isTransfer"=>1,"short_url"=>"SaigonBank","logo"=>"https://api.viqr.net/icons/SGICB.5886546f.png","support"=>3],
        ["id"=>9,"name"=>"Ngân hàng TMCP Đông Á","code"=>"DOB","bin"=>"970406","isTransfer"=>0,"short_url"=>"DongABank","logo"=>"https://api.viqr.net/icons/DOB.92bbf6f4.png","support"=>0],
        ["id"=>3,"name"=>"Ngân hàng TMCP Bắc Á","code"=>"BAB","bin"=>"970409","isTransfer"=>0,"short_url"=>"BacABank","logo"=>"https://api.viqr.net/icons/BAB.75c3a8c2.png","support"=>0],
        ["id"=>32,"name"=>"Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam","code"=>"SCVN","bin"=>"970410","isTransfer"=>0,"short_url"=>"StandardChartered","logo"=>"https://api.viqr.net/icons/SCVN.a53976be.png","support"=>0],
        ["id"=>27,"name"=>"Ngân hàng Thương mại TNHH MTV Đại Dương","code"=>"Oceanbank","bin"=>"970414","isTransfer"=>0,"short_url"=>"Oceanbank","logo"=>"https://api.viqr.net/icons/OCEANBANK.f84c3119.png","support"=>0],
        ["id"=>48,"name"=>"Ngân hàng Liên doanh Việt - Nga","code"=>"VRB","bin"=>"970421","isTransfer"=>0,"short_url"=>"VRB","logo"=>"https://api.viqr.net/icons/VRB.9d6d40f3.png","support"=>0],
        ["id"=>1,"name"=>"Ngân hàng TMCP An Bình","code"=>"ABB","bin"=>"970425","isTransfer"=>0,"short_url"=>"ABBANK","logo"=>"https://api.viqr.net/icons/ABB.9defb03d.png","support"=>0],
        ["id"=>41,"name"=>"Ngân hàng TMCP Việt Á","code"=>"VAB","bin"=>"970427","isTransfer"=>0,"short_url"=>"VietABank","logo"=>"https://api.viqr.net/icons/VAB.9bf85d8e.png","support"=>0],
        ["id"=>10,"name"=>"Ngân hàng TMCP Xuất Nhập khẩu Việt Nam","code"=>"EIB","bin"=>"970431","isTransfer"=>0,"short_url"=>"Eximbank","logo"=>"https://api.viqr.net/icons/EIB.ae2f0252.png","support"=>0],
        ["id"=>46,"name"=>"Ngân hàng TMCP Việt Nam Thương Tín","code"=>"VIETBANK","bin"=>"970433","isTransfer"=>1,"short_url"=>"VietBank","logo"=>"https://api.viqr.net/icons/VIETBANK.bb702d50.png","support"=>3],
        ["id"=>18,"name"=>"Ngân hàng TNHH Indovina","code"=>"IVB","bin"=>"970434","isTransfer"=>0,"short_url"=>"IndovinaBank","logo"=>"https://api.viqr.net/icons/IVB.ee79782c.png","support"=>0],
        ["id"=>5,"name"=>"Ngân hàng TMCP Bảo Việt","code"=>"BVB","bin"=>"970438","isTransfer"=>1,"short_url"=>"BaoVietBank","logo"=>"https://api.viqr.net/icons/BVB.2b7aab15.png","support"=>3],
        ["id"=>28,"name"=>"Ngân hàng TNHH MTV Public Việt Nam","code"=>"PBVN","bin"=>"970439","isTransfer"=>0,"short_url"=>"PublicBank","logo"=>"https://api.viqr.net/icons/PBVN.67dbc9af.png","support"=>0],
        ["id"=>35,"name"=>"Ngân hàng TMCP Sài Gòn - Hà Nội","code"=>"SHB","bin"=>"970443","isTransfer"=>0,"short_url"=>"SHB","logo"=>"https://api.viqr.net/icons/SHB.665daa27.png","support"=>0],
        ["id"=>6,"name"=>"Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam","code"=>"CBB","bin"=>"970444","isTransfer"=>0,"short_url"=>"CBBank","logo"=>"https://api.viqr.net/icons/CBB.5b47e56f.png","support"=>0],
        ["id"=>26,"name"=>"Ngân hàng TMCP Phương Đông","code"=>"OCB","bin"=>"970448","isTransfer"=>1,"short_url"=>"OCB","logo"=>"https://api.viqr.net/icons/OCB.84d922d1.png","support"=>3],
        ["id"=>19,"name"=>"Ngân hàng TMCP Kiên Long","code"=>"KLB","bin"=>"970452","isTransfer"=>1,"short_url"=>"KienLongBank","logo"=>"https://api.viqr.net/icons/KLB.23902895.png","support"=>3],
        ["id"=>7,"name"=>"Ngân hàng TNHH MTV CIMB Việt Nam","code"=>"CIMB","bin"=>"422589","isTransfer"=>0,"short_url"=>"CIMB","logo"=>"https://api.viqr.net/icons/CIMB.70b35f80.png","support"=>0],
        ["id"=>14,"name"=>"Ngân hàng TNHH MTV HSBC (Việt Nam)","code"=>"HSBC","bin"=>"458761","isTransfer"=>0,"short_url"=>"HSBC","logo"=>"https://api.viqr.net/icons/HSBC.6fa79196.png","support"=>0],
        ["id"=>8,"name"=>"DBS Bank Ltd - Chi nhánh Thành phố Hồ Chí Minh","code"=>"DBS","bin"=>"796500","isTransfer"=>0,"short_url"=>"DBSBank","logo"=>"https://api.viqr.net/icons/DBS.83742b1e.png","support"=>0],
        ["id"=>25,"name"=>"Ngân hàng Nonghyup - Chi nhánh Hà Nội","code"=>"NHB HN","bin"=>"801011","isTransfer"=>0,"short_url"=>"Nonghyup","logo"=>"https://api.viqr.net/icons/NHB%20HN.6a3f7952.png","support"=>0],
        ["id"=>13,"name"=>"Ngân hàng TNHH MTV Hong Leong Việt Nam","code"=>"HLBVN","bin"=>"970442","isTransfer"=>0,"short_url"=>"HongLeong","logo"=>"https://api.viqr.net/icons/HLBVN.4a284a9a.png","support"=>0],
        ["id"=>15,"name"=>"Ngân hàng Công nghiệp Hàn Quốc - Chi nhánh Hà Nội","code"=>"IBK - HN","bin"=>"970455","isTransfer"=>0,"short_url"=>"IBK Bank","logo"=>"https://api.viqr.net/icons/IBK%20-%20HN.eee4e569.png","support"=>0],
        ["id"=>16,"name"=>"Ngân hàng Công nghiệp Hàn Quốc - Chi nhánh TP. Hồ Chí Minh","code"=>"IBK - HCM","bin"=>"970456","isTransfer"=>0,"short_url"=>"IBK Bank","logo"=>"https://api.viqr.net/icons/IBK%20-%20HN.eee4e569.png","support"=>0],
        ["id"=>49,"name"=>"Ngân hàng TNHH MTV Woori Việt Nam","code"=>"WVN","bin"=>"970457","isTransfer"=>0,"short_url"=>"Woori","logo"=>"https://api.viqr.net/icons/WVN.45451999.png","support"=>0],
        ["id"=>40,"name"=>"Ngân hàng United Overseas - Chi nhánh TP. Hồ Chí Minh","code"=>"UOB","bin"=>"970458","isTransfer"=>0,"short_url"=>"UnitedOverseas","logo"=>"https://api.viqr.net/icons/UOB.e6a847d2.png","support"=>0],
        ["id"=>50,"name"=>"Ngân hàng Kookmin - Chi nhánh Hà Nội","code"=>"KBHN","bin"=>"970462","isTransfer"=>0,"short_url"=>"KookminHN","logo"=>"https://api.viqr.net/icons/KBHN.5126abce.png","support"=>0],
        ["id"=>51,"name"=>"Ngân hàng Kookmin - Chi nhánh Thành phố Hồ Chí Minh","code"=>"KBHCM","bin"=>"970463","isTransfer"=>0,"short_url"=>"KookminHCM","logo"=>"https://api.viqr.net/icons/KBHN.5126abce.png","support"=>0],
        ["id"=>52,"name"=>"Ngân hàng Hợp tác xã Việt Nam","code"=>"COOPBANK","bin"=>"970446","isTransfer"=>0,"short_url"=>"COOPBANK","logo"=>"https://api.viqr.net/icons/COOPBANK.16fc2602.png","support"=>0]
        ];
    }
    public static function getAction(){
        $curent_route = Route::current();
        $action = @$curent_route->action['permission'];
        if ($action) {
           return $action;
        }
        return null;
    }
    public static function validateDate($date, $format = 'd/m/Y') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    public static function handler($value, $type_model = null)
    {
        if($type_model == 'service'){
            $result = Service::get_detail_bdc_service_by_bdc_service_id($value);
            if($result) return json_encode($result);
            return null;
        }
        if($type_model == 'apartment'){
            $result = Apartments::get_detail_apartment_by_apartment_id($value);
            if($result) return json_encode($result);
            return null;
        }
        if($type_model == 'promotion'){
            $result = Promotion::find($value);
            if($result) return json_encode($result);
            return null;
        }
        if($type_model == 'service_apartment'){
            $result = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value);
            if($result) return json_encode($result);
            return null;
        }
    }
    public static function decode_string($value)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        },$value);
    }
}
