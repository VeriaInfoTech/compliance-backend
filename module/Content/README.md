# Content

Pi content management by laminas

## Information json/array format

This is standard for each type format

### Type: `page`

page type for static pages

Json format:

```
{
    "body": {
        "title": "Main Title",
        "slug": "main-title",
        "type": "page",
        "summery": "This is a test content",
        "time_create": "2022/01/01",
        "time_update": "2022/01/01",
        "section-one": {
            "title": "Section title",
            "sub-title": "Section sub title",
            "description": "description description description description",
            "image": "",
            "icon": "",
            "video": "",
            "items": [
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                }
            ]
        },
        "section-two": {
            "title": "Section title",
            "sub-title": "Section sub title",
            "description": "description description description description",
            "image": "",
            "icon": "",
            "video": "",
            "items": [
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                }
            ]
        },
        "section-three": {
            "title": "Section title",
            "sub-title": "Section sub title",
            "description": "description description description description",
            "image": "",
            "icon": "",
            "video": "",
            "items": [
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                },
                {
                    "title": "Section title",
                    "sub-title": "Section sub title",
                    "description": "description description description description",
                    "image": "",
                    "icon": "",
                    "video": "",
                    "link": "",
                    "link-title": ""
                }
            ]
        }
    },
    "media": {
        "image": {
            "thumbnail": "thumbnail.jpg",
            "medium": "medium.jpg",
            "large": "large.jpg"
        },
        "gallery": [
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            },
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            },
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            }
        ],
        "videos": {
            "low": "hls url",
            "medium": "hls url",
            "high": "hls url"
        }
    },
    "meta": {
        "title": "",
        "keyboard": "",
        "description": ""
    },
    "schema": [],
    "twitter-cards": [],
    "open-graph": []
}
```

PHP array format:

``` 
[
    'body'          => [
        'title'       => 'Main Title',
        'slug'        => 'main-title',
        'type'        => 'page',
        'summery'     => 'This is a test content',
        'time_create' => '2022/01/01',
        'time_update' => '2022/01/01',
        'section-one' => [
            'title' => 'Section title',
            'sub-title' => 'Section sub title',
            'description' => 'description description description description',
            'image' => '',
            'icon' => '',
            'video' => '',
            'items' => [
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
            ]
        ],
        'section-two' => [
            'title' => 'Section title',
            'sub-title' => 'Section sub title',
            'description' => 'description description description description',
            'image' => '',
            'icon' => '',
            'video' => '',
            'items' => [
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
            ]
        ],
        'section-three' => [
            'title' => 'Section title',
            'sub-title' => 'Section sub title',
            'description' => 'description description description description',
            'image' => '',
            'icon' => '',
            'video' => '',
            'items' => [
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
                [
                    'title' => 'Section title',
                    'sub-title' => 'Section sub title',
                    'description' => 'description description description description',
                    'image' => '',
                    'icon' => '',
                    'video' => '',
                    'link' => '',
                    'link-title' => '',
                ],
            ]
        ],
    ],
    'media'         => [
        'image'   => [
            'thumbnail' => 'thumbnail.jpg',
            'medium'    => 'medium.jpg',
            'large'     => 'large.jpg',
        ],
        'gallery' => [
            [
                'title'     => 'Image title',
                'thumbnail' => 'thumbnail.jpg',
                'medium'    => 'medium.jpg',
                'large'     => 'large.jpg',
            ],
        ],
        'videos'  => [
            'low'    => 'hls url',
            'medium' => 'hls url',
            'high'   => 'hls url',
        ],
    ],
    'meta'          => [
        'title'       => '',
        'keyboard'    => '',
        'description' => '',
    ],
    'schema'        => [],
    'twitter-cards' => [],
    'open-graph'    => [],
]
```

### Type: `category`

category type for all topics and categories

Json format:

```
{
    "body": {
        "title": "Main Title",
        "slug": "main-title",
        "type": "category",
        "summery": "This is a test content",
        "time_create": "2022/01/01",
        "time_update": "2022/01/01",
        "detail": [
            {
                "sub-title": "",
                "description": "",
                "items": [
                    {
                        "title": "Sub title 1",
                        "description": "description description description description",
                        "icon": "",
                        "image": "img.jpg",
                        "video": ""
                    }
                ]
            },
            {
                "sub-title": "",
                "description": "",
                "items": [
                    {
                        "title": "Sub title 1",
                        "description": "description description description description",
                        "icon": "",
                        "image": "img.jpg",
                        "video": ""
                    }
                ]
            }
        ],
        "sub-categories": [
            {
                "title": "",
                "slug": "",
                "image": "",
                "summery": ""
            },
            {
                "title": "",
                "slug": "",
                "image": "",
                "summery": ""
            },
            {
                "title": "",
                "slug": "",
                "image": "",
                "summery": ""
            },
            {
                "title": "",
                "slug": "",
                "image": "",
                "summery": ""
            }
        ]
    },
    "media": {
        "image": {
            "thumbnail": "thumbnail.jpg",
            "medium": "medium.jpg",
            "large": "large.jpg"
        },
        "slide": [
            {
                "title": "",
                "description": "",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg",
                "link": "",
                "link-title": ""
            },
            {
                "title": "",
                "description": "",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg",
                "link": "",
                "link-title": ""
            },
            {
                "title": "",
                "description": "",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg",
                "link": "",
                "link-title": ""
            }
        ]
    },
    "meta": {
        "title": "",
        "keyboard": "",
        "description": ""
    },
    "schema": [],
    "twitter-cards": [],
    "open-graph": []
}
```

PHP array format:

``` 
[
    'body'          => [
        'title'       => 'Main Title',
        'slug'        => 'main-title',
        'type'        => 'category',
        'summery'     => 'This is a test content',
        'time_create' => '2022/01/01',
        'time_update' => '2022/01/01',
        'detail'      => [
            [
                'sub-title'   => '',
                'description' => '',
                'items'       => [
                    [
                        'title'       => 'Sub title 1',
                        'description' => 'description description description description',
                        'icon'        => '',
                        'image'       => 'img.jpg',
                        'video'       => '',
                    ],
                ],
            ],
            [
                'sub-title'   => '',
                'description' => '',
                'items'       => [
                    [
                        'title'       => 'Sub title 1',
                        'description' => 'description description description description',
                        'icon'        => '',
                        'image'       => 'img.jpg',
                        'video'       => '',
                    ],
                ],
            ],
        ],
        'sub-categories' => [
            [
                'title' => '',
                'slug' => '',
                'image' => '',
                'summery' => '',
            ],
            [
                'title' => '',
                'slug' => '',
                'image' => '',
                'summery' => '',
            ],
            [
                'title' => '',
                'slug' => '',
                'image' => '',
                'summery' => '',
            ],
            [
                'title' => '',
                'slug' => '',
                'image' => '',
                'summery' => '',
            ],
        ],
    ],
    'media'         => [
        'image'   => [
            'thumbnail' => 'thumbnail.jpg',
            'medium'    => 'medium.jpg',
            'large'     => 'large.jpg',
        ],
        'slide'   => [
            [
                'title' => '',
                'description' => '',
                'thumbnail' => 'thumbnail.jpg',
                'medium'    => 'medium.jpg',
                'large'     => 'large.jpg',
                'link' => '',
                'link-title' => '',
            ],
            [
                'title' => '',
                'description' => '',
                'thumbnail' => 'thumbnail.jpg',
                'medium'    => 'medium.jpg',
                'large'     => 'large.jpg',
                'link' => '',
                'link-title' => '',
            ],
            [
                'title' => '',
                'description' => '',
                'thumbnail' => 'thumbnail.jpg',
                'medium'    => 'medium.jpg',
                'large'     => 'large.jpg',
                'link' => '',
                'link-title' => '',
            ]
        ],
    ],
    'meta'          => [
        'title'       => '',
        'keyboard'    => '',
        'description' => '',
    ],
    'schema'        => [],
    'twitter-cards' => [],
    'open-graph'    => [],
]
```

### Type: `product`

product type for shopping

Json format:

```
{
    "body": {
        "title": "Main Title",
        "slug": "main-title",
        "type": "product",
        "summery": "This is a test content",
        "time_create": "2022/01/01",
        "time_update": "2022/01/01",
        "detail": [
            {
                "sub-title": "",
                "description": "",
                "items": [
                    {
                        "title": "Sub title 1",
                        "description": "description description description description",
                        "icon": "",
                        "image": "img.jpg",
                        "video": ""
                    }
                ]
            },
            {
                "sub-title": "",
                "description": "",
                "items": [
                    {
                        "title": "Sub title 1",
                        "description": "description description description description",
                        "icon": "",
                        "image": "img.jpg",
                        "video": ""
                    }
                ]
            }
        ],
        "review": {
            "title": "",
            "description": "",
            "items": [
                {
                    "title": "Sub title 1",
                    "description": "description description description description",
                    "icon": "",
                    "image": "img.jpg",
                    "video": ""
                },
                {
                    "title": "Sub title 2",
                    "description": "description description description description",
                    "icon": "",
                    "image": "img.jpg",
                    "video": ""
                },
                {
                    "title": "Sub title 3",
                    "description": "description description description description",
                    "icon": "",
                    "image": "img.jpg",
                    "video": ""
                }
            ]
        }
    },
    "media": {
        "image": {
            "thumbnail": "thumbnail.jpg",
            "medium": "medium.jpg",
            "large": "large.jpg"
        },
        "gallery": [
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            }
        ],
        "videos": {
            "low": "hls url",
            "medium": "hls url",
            "high": "hls url"
        }
    },
    "meta": {
        "title": "",
        "keyboard": "",
        "description": ""
    },
    "schema": [],
    "twitter-cards": [],
    "open-graph": [],
    "price": [],
    "attribute": [],
    "property": []
}
```

PHP array format:

```
[
    'body'          => [
        'title'       => 'Main Title',
        'slug'        => 'main-title',
        'type'        => 'product',
        'summery'     => 'This is a test content',
        'time_create' => '2022/01/01',
        'time_update' => '2022/01/01',
        'detail'      => [
            [
                'sub-title'   => '',
                'description' => '',
                'items'       => [
                    [
                        'title'       => 'Sub title 1',
                        'description' => 'description description description description',
                        'icon'        => '',
                        'image'       => 'img.jpg',
                        'video'       => '',
                    ],
                ],
            ],
            [
                'sub-title'   => '',
                'description' => '',
                'items'       => [
                    [
                        'title'       => 'Sub title 1',
                        'description' => 'description description description description',
                        'icon'        => '',
                        'image'       => 'img.jpg',
                        'video'       => '',
                    ],
                ],
            ],
        ],
        'review'      => [
            'title'       => '',
            'description' => '',
            'items'       => [
                [
                    'title'       => 'Sub title 1',
                    'description' => 'description description description description',
                    'icon'        => '',
                    'image'       => 'img.jpg',
                    'video'       => '',
                ],
                [
                    'title'       => 'Sub title 2',
                    'description' => 'description description description description',
                    'icon'        => '',
                    'image'       => 'img.jpg',
                    'video'       => '',
                ],
                [
                    'title'       => 'Sub title 3',
                    'description' => 'description description description description',
                    'icon'        => '',
                    'image'       => 'img.jpg',
                    'video'       => '',
                ],
            ],
        ],
    ],
    'media'         => [
        'image'   => [
            'thumbnail' => 'thumbnail.jpg',
            'medium'    => 'medium.jpg',
            'large'     => 'large.jpg',
        ],
        "gallery": [
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            },
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            },
            {
                "title": "Image title",
                "thumbnail": "thumbnail.jpg",
                "medium": "medium.jpg",
                "large": "large.jpg"
            }
        ],
        'videos'  => [
            'low'    => 'hls url',
            'medium' => 'hls url',
            'high'   => 'hls url',
        ],
    ],
    'meta'          => [
        'title'       => '',
        'keyboard'    => '',
        'description' => '',
    ],
    'schema'        => [],
    'twitter-cards' => [],
    'open-graph'    => [],
    'price'         => [],
    'attribute'     => [],
    'property'      => [],
]
```