<?php

return [
    'admin'    => [
        'title' => 'Quản lý Hệ thống',
        'group' => [
            [
                'title'       => 'Quản trị viên',
                'permissions' => [
                    'admin.root' => 'Quyền tối cao',
                ],
            ],
        ],
    ],

    'user'     => [
        'title' => 'Quản lý Nhân viên',
        'group' => [
            [
                'title'       => 'Nhân viên',
                'permissions' => [
                    'bo_users.index'  => 'Hiển thị danh sách',
                    'bo_users.view'   => 'Xem thông tin chi tiết',
                    'bo_users.update' => 'Thêm mới/Sửa',
                    'bo_users.delete' => 'Xóa',
                ],
            ],
            // [
            //     'title'       => 'Phòng ban',
            //     'permissions' => [
            //         'bo_user_groups.index' => 'Hiển thị danh sách',
            //     ],
            // ],
            [
                'title'       => 'Nhóm quyền',
                'permissions' => [
                    'roles.index'        => 'Hiển thị danh sách',
                    'roles.view'         => 'Xem thông tin chi tiết',
                    'roles.update'       => 'Thêm mới/Sửa nhóm quyền',
                    'roles.delete'       => 'Xóa nhóm quyền',

                    'role_users.view'    => 'Xem danh sách người dùng',
                    'role_users.approve' => 'Thêm người dùng',
                    'role_users.delete'  => 'Xóa người dùng',
                ],
            ],
        ],
    ],
    'user_ctv' => [
        'title' => 'CTV gọi điện',
        'group' => [
            [
                'title'       => 'Danh sách CTV',
                'permissions' => [
                    'campaign_assigns.index_ctv'  => 'Hiển thị danh sách',
                    'campaign_assigns.index_ctv_kh'  => 'Khách hàng phân bổ',
                    'campaign_assigns.view_ctv'   => 'Danh sách CTV',
                    'campaign_assigns.update_ctv' => 'Thêm mới/Sửa',
                    'campaign_assigns.alloctions' => 'Phân bổ Cộng tác viên',
                    'campaign_assigns.delete_ctv' => 'Xóa',
                ],
            ],
        ],
    ],
    'article'  => [
        'title' => 'Quản lý bài viết',
        'group' => [
            [
                'title'       => 'Danh mục',
                'permissions' => [
                    'categories.article.index'  => 'Hiển thị danh sách',
                    'categories.article.view'   => 'Xem thông tin chi tiết',
                    'categories.article.update' => 'Thêm mới/Sửa',
                    'categories.article.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Bài viết',
                'permissions' => [
                    'posts.article.index'  => 'Hiển thị danh sách',
                    'posts.article.view'   => 'Xem thông tin chi tiết',
                    'posts.article.update' => 'Thêm mới/Sửa',
                    'posts.article.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Bình luận',
                'permissions' => [
                    'comments.article.index'   => 'Hiển thị danh sách',
                    'comments.article.view'    => 'Xem chi tiết',
                    'comments.article.reply'   => 'Trả lời bình luận',
                    'comments.article.approve' => 'Duyệt bình luận',
                    'comments.article.delete'  => 'Xóa bình luận',
                ],
            ],
        ],
    ],

    'event'    => [
        'title' => 'Quản lý Sự kiện',
        'group' => [
            [
                'title'       => 'Danh mục',
                'permissions' => [
                    'categories.event.index'  => 'Hiển thị danh sách',
                    'categories.event.view'   => 'Xem thông tin chi tiết',
                    'categories.event.update' => 'Thêm mới/Sửa',
                    'categories.event.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Sự kiện',
                'permissions' => [
                    'posts.event.index'           => 'Hiển thị danh sách',
                    'posts.event.view'            => 'Xem thông tin chi tiết',
                    'posts.event.update'          => 'Thêm mới/Sửa sự kiện',
                    'posts.event.delete'          => 'Xóa sự kiện',
                    'post_registers.event.index'  => 'Hiển thị danh sách đăng ký',
                    'post_registers.event.export' => 'Xuất danh sách đăng ký',
                ],
            ],
            [
                'title'       => 'Bình luận',
                'permissions' => [
                    'comments.event.index'   => 'Hiển thị danh sách',
                    'comments.event.view'    => 'Xem chi tiết',
                    'comments.event.reply'   => 'Trả lời bình luận',
                    'comments.event.approve' => 'Duyệt bình luận',
                    'comments.event.delete'  => 'Xóa bình luận',
                ],
            ],
        ],
    ],

    'voucher'  => [
        'title' => 'Quản lý Voucher',
        'group' => [
            [
                'title'       => 'Danh mục',
                'permissions' => [
                    'categories.voucher.index'  => 'Hiển thị danh sách',
                    'categories.voucher.view'   => 'Xem thông tin chi tiết',
                    'categories.voucher.update' => 'Thêm mới/Sửa',
                    'categories.voucher.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Voucher',
                'permissions' => [
                    'posts.voucher.index'            => 'Hiển thị danh sách',
                    'posts.voucher.view'             => 'Xem thông tin chi tiết',
                    'posts.voucher.update'           => 'Thêm mới/Sửa',
                    'posts.voucher.delete'           => 'Xóa',
                    'post_registers.voucher.index'   => 'Hiển thị danh sách đăng ký',
                    'post_registers.voucher.export'  => 'Xuất danh sách đăng ký',
                    'post_registers.voucher.checkin' => 'Check in khách hàng',
                ],
            ],
            [
                'title'       => 'Bình luận',
                'permissions' => [
                    'comments.voucher.index'   => 'Hiển thị danh sách',
                    'comments.voucher.view'    => 'Xem chi tiết',
                    'comments.voucher.reply'   => 'Trả lời bình luận',
                    'comments.voucher.approve' => 'Duyệt bình luận',
                    'comments.voucher.delete'  => 'Xóa bình luận',
                ],
            ],
        ],
    ],

    'partner'  => [
        'title' => 'Quản lý Đối tác',
        'group' => [
            [
                'title'       => 'Đối tác',
                'permissions' => [
                    'partners.index'  => 'Hiển thị danh sách',
                    'partners.view'   => 'Xem thông tin chi tiết',
                    'partners.update' => 'Thêm mới/Sửa đối tác',
                    'partners.delete' => 'Xóa đối tác',
                ],
            ],
            [
                'title'       => 'Chi nhánh',
                'permissions' => [
                    'branches.index'  => 'Hiển thị danh sách',
                    'branches.view'   => 'Xem thông tin chi tiết',
                    'branches.update' => 'Thêm mới/Sửa',
                    'branches.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Tài khoản đối tác',
                'permissions' => [
                    'user_partners.index'  => 'Hiển thị danh sách',
                    'user_partners.view'   => 'Xem thông tin chi tiết',
                    'user_partners.update' => 'Thêm mới/sửa',
                    'user_partners.delete' => 'Xóa',
                ],
            ],
        ],
    ],

    'campaign' => [
        'title' => 'Quản lý chiến dịch',
        'group' => [
            [
                'title'       => 'Chiến dịch',
                'permissions' => [
                    'campaigns.index'  => 'Hiển thị danh sách',
                    'campaigns.view'   => 'Xem thông tin chi tiết',
                    'campaigns.update' => 'Thêm mới/Sửa',
                    'campaigns.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Phân bổ khách hàng',
                'permissions' => [
                    'campaign_assigns.index'  => 'Hiển thị danh sách',
                    'campaign_assigns.view'   => 'Xem thông tin chi tiết',
                    'campaign_assigns.update' => 'Thêm mới/Sửa',
                    'campaign_assigns.delete' => 'Xóa',
                ],
            ],
        ],
    ],

    'customer' => [
        'title' => 'Quản lý Khách hàng',
        'group' => [
            [
                'title'       => 'Khách hàng',
                'permissions' => [
                    'b_o_customers.index'  => 'Hiển thị danh sách',
                    'b_o_customers.view'   => 'Xem thông tin chi tiết',
                    'b_o_customers.update' => 'Thêm mới/Sửa',
                    'b_o_customers.delete' => 'Xóa',
                    'b_o_customers.export' => 'Xuất danh sách khách hàng',
                    'b_o_customers.assign' => 'Phân bổ khách hàng',
                ],
            ],
            [
                'title'       => 'Nhóm khách hàng',
                'permissions' => [
                    'customer_groups.index'  => 'Hiển thị danh sách',
                    'customer_groups.view'   => 'Xem thông tin chi tiết',
                    'customer_groups.update' => 'Thêm mới/Sửa',
                    'customer_groups.delete' => 'Xóa',
                ],
            ],
            [
                'title'       => 'Nhật ký khách hàng',
                'permissions' => [
                    'customer_diaries.index'  => 'Hiển thị danh sách',
                    'customer_diaries.view'   => 'Xem thông tin chi tiết',
                    'customer_diaries.update' => 'Thêm mới/Sửa',
                    'customer_diaries.delete' => 'Xóa',
                ],
            ],
        ],
    ],

    'exchange' => [
        'title' => 'Quản lý điểm giao dịch',
        'group' => [
            [
                'title'       => 'Điểm giao dịch',
                'permissions' => [
                    'exchanges.index'  => 'Hiển thị danh sách',
                    'exchanges.view'   => 'Xem thông tin chi tiết',
                    'exchanges.update' => 'Thêm mới/Sửa',
                    'exchanges.delete' => 'Xóa',
                ],
            ],
        ],
    ],

    'feedback' => [
        'title' => 'Quản lý Ý kiến phản hồi',
        'group' => [
            [
                'title'       => 'Ý kiến phản hồi',
                'permissions' => [
                    'feedback.index'   => 'Hiển thị danh sách',
                    'feedback.view'    => 'Xem thông tin chi tiết',
                    'feedback.reply'   => 'Trả lời ý kiến',
                    'feedback.approve' => 'Duyệt ý kiến',
                    'feedback.delete'  => 'Xóa ý kiến',
                ],
            ],
        ],
    ],

    'setting'  => [
        'title' => 'Quản lý setting',
        'group' => [
            [
                'title'       => 'Setting',
                'permissions' => [
                    'settings.index'  => 'Hiển thị danh sách',
                    'settings.update' => 'Thêm mới',
                    'settings.edit'   => 'Sửa thông tin',
                    'settings.delete' => 'Xóa',
                ],
            ],
        ],
    ],

];
