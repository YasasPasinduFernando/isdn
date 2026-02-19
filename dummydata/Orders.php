<?php
$orders = [
    [
        "id" => 1,
        "order_date" => "2026-02-13",
        "customer" => "Vijaya Stores - Galle",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260213-1025",
        "total_amount" => 18751.46,
        "status" => "Pending",
        "estimated_date" => "2026-02-15",
        "item_count" => 5,
        "created_at" => "2026-02-11 12:20:52",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 2,
        "order_date" => "2026-02-10",
        "customer" => "Vijaya Stores - Galle",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260211-4103",
        "total_amount" => 18650.00,
        "status" => "Processing",
        "estimated_date" => "2026-02-12",
        "item_count" => 3,

        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 3,
        "order_date" => "2026-02-09",
        "customer" => "Vijaya Stores - Galle",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260209-1452",
        "total_amount" => 18650.00,
        "status" => "Delivered",
        "estimated_date" => "2026-02-11",
        "item_count" => 6,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 4,
        "order_date" => "2026-02-08",
        "customer" => "Vijaya Stores - Galle",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260205-5452",
        "total_amount" => 15356.00,
        "status" => "In Transit",
        "estimated_date" => "2026-02-07",
        "item_count" => 4,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
    ,
    [
        "id" => 5,
        "order_date" => "2026-02-06",
        "customer" => "Vijaya Stores - Galle",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260208-5461",
        "total_amount" => 17365.00,
        "status" => "Cancelled",
        "estimated_date" => "2026-02-08",
        "item_count" => 3,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
];

$refOrders = [
    [
        "id" => 1,
        "order_date" => "2026-02-11",
        "customer" => "Wijitha Stores",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260211-6542",
        "total_amount" => 25300.00,
        "status" => "Pending",
        "estimated_date" => "2026-02-13",
        "item_count" => 5,
        "created_at" => "2026-02-11 12:20:52",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 2,
        "order_date" => "2026-02-10",
        "customer" => "Amal Stores",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260211-2546",
        "total_amount" => 18650.00,
        "status" => "Processing",
        "estimated_date" => "2026-02-12",
        "item_count" => 3,

        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 3,
        "order_date" => "2026-02-09",
        "customer" => "Vijaya Stores",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260209-2545",
        "total_amount" => 18650.00,
        "status" => "Delivered",
        "estimated_date" => "2026-02-11",
        "item_count" => 6,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 4,
        "order_date" => "2026-02-08",
        "customer" => "Shanthi Stores",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260205-7854",
        "total_amount" => 15356.00,
        "status" => "Delivered",
        "estimated_date" => "2026-02-07",
        "item_count" => 4,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
    ,
    [
        "id" => 5,
        "order_date" => "2026-02-06",
        "customer" => "LGJ Super Store",
        "placed_by" => "",
        "order_number" => "ORD-RDCS-260208-6984",
        "total_amount" => 17365.00,
        "status" => "Cancelled",
        "estimated_date" => "2026-02-08",
        "item_count" => 3,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
];

$clerkOrders = [
    [
        "id" => 1,
        "order_date" => "2026-02-11",
        "customer" => "Wijitha Stores",
        "sales_ref" => "Sajith Perera",
        "order_number" => "ORD-RDCS-260211-6542",
        "total_amount" => 25300.00,
        "status" => "Pending",
        "estimated_date" => "2026-02-13",
        "item_count" => 5,
        "created_at" => "2026-02-11 12:20:52",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 2,
        "order_date" => "2026-02-10",
        "customer" => "Amal Stores",
        "sales_ref" => "Kamal De Silva",
        "order_number" => "ORD-RDCS-260211-2546",
        "total_amount" => 18650.00,
        "status" => "Processing",
        "estimated_date" => "2026-02-12",
        "item_count" => 3,

        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 3,
        "order_date" => "2026-02-09",
        "customer" => "Vijaya Stores",
        "sales_ref" => "Kusal Mendis",
        "order_number" => "ORD-RDCS-260209-2545",
        "total_amount" => 18650.00,
        "status" => "In Transit",
        "estimated_date" => "2026-02-11",
        "item_count" => 6,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 4,
        "order_date" => "2026-02-08",
        "customer" => "Shanthi Stores",
        "sales_ref" => "Pathum Nissanka",
        "order_number" => "ORD-RDCS-260205-7854",
        "total_amount" => 15356.00,
        "status" => "Delivered",
        "estimated_date" => "2026-02-07",
        "item_count" => 4,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
    ,
    [
        "id" => 5,
        "order_date" => "2026-02-06",
        "customer" => "LGJ Super Store",
        "sales_ref" => "Kamindu Mendis",
        "order_number" => "ORD-RDCS-260208-6984",
        "total_amount" => 17365.00,
        "status" => "Cancelled",
        "estimated_date" => "2026-02-08",
        "item_count" => 3,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
];

$headOfficeOrders = [
    [
        "id" => 1,
        "order_date" => "2026-02-11",
        "customer" => "Wijitha Stores",
        "sales_ref" => "Sajith Perera",
        "rdc" => "Southern",
        "order_number" => "ORD-RDCS-260211-6542",
        "total_amount" => 25300.00,
        "status" => "Pending",
        "estimated_date" => "2026-02-13",
        "item_count" => 5,
        "created_at" => "2026-02-11 12:20:52",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 2,
        "order_date" => "2026-02-10",
        "customer" => "Amal Stores",
        "sales_ref" => "Kamal De Silva",
        "rdc" => "Western",
        "order_number" => "ORD-RDCS-260211-2546",
        "total_amount" => 18650.00,
        "status" => "Processing",
        "estimated_date" => "2026-02-12",
        "item_count" => 3,

        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 3,
        "order_date" => "2026-02-09",
        "customer" => "Vijaya Stores",
        "sales_ref" => "Kusal Mendis",
        "rdc" => "Southern",
        "order_number" => "ORD-RDCS-260209-2545",
        "total_amount" => 18650.00,
        "status" => "In Transit",
        "estimated_date" => "2026-02-11",
        "item_count" => 6,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ],
    [
        "id" => 4,
        "order_date" => "2026-02-08",
        "customer" => "Shanthi Stores",
        "sales_ref" => "Pathum Nissanka",
        "rdc" => "Eastern",
        "order_number" => "ORD-RDCS-260205-7854",
        "total_amount" => 15356.00,
        "status" => "Delivered",
        "estimated_date" => "2026-02-07",
        "item_count" => 4,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
    ,
    [
        "id" => 5,
        "order_date" => "2026-02-06",
        "customer" => "LGJ Super Store",
        "sales_ref" => "Kamindu Mendis",
        "rdc" => "Central",
        "order_number" => "ORD-RDCS-260208-6984",
        "total_amount" => 17365.00,
        "status" => "Cancelled",
        "estimated_date" => "2026-02-08",
        "item_count" => 3,
        "created_at" => "2026-02-11 13:18:33",
        "updated_at" => "2026-02-11 13:23:45"
    ]
];