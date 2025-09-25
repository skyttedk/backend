<?php


$response = [
    "status" => "success",
    "data" => [
        [
            "id" => 1,
            "title" => "Første item",
            "description" => "Dette er beskrivelsen af det første item",
            "createdAt" => "2024-03-10T08:30:00Z",
            "status" => "active",
            "category" => "kategori1"
        ],
        [
            "id" => 2,
            "title" => "Andet item",
            "description" => "En længere beskrivelse af det andet item med mere detaljer om hvad det indeholder",
            "createdAt" => "2024-03-09T15:45:00Z",
            "status" => "pending",
            "category" => "kategori2"
        ],
        [
            "id" => 3,
            "title" => "Tredje item",
            "description" => "Kort beskrivelse",
            "createdAt" => "2024-03-08T11:20:00Z",
            "status" => "inactive",
            "category" => "kategori1"
        ]
    ],
    "meta" => [
        "total" => 3,
        "page" => 1,
        "perPage" => 10,
        "totalPages" => 1
    ]
];

echo json_encode($response);