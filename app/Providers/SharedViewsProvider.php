<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SharedViewsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     * @throws Exception
     */
    public function boot()
    {
        $links = [
            'creators' => [
                'name' => 'Authors/Partners',
                'icon' => 'book',
                'children' => [
                    '/system/creators/p' => [
                        'name' => 'Statistic',
                    ],
                    '/system/creators/p/payouts' => [
                        'name' => 'Payouts',
                    ],
                    '/system/creators/a/submissions' => [
                        'name' => 'Submissions',
                    ],
                    '/system/creators/a/view/applicants' => [
                        'name' => 'Applicants',
                    ]
                ]
            ],
            'users' => [
                'name' => 'Users',
                'icon' => 'people',
                'route' => '/system/users'
            ],
            'main-page' => [
                'name' => 'Main Page',
                'icon' => 'heart',
                'children' => [
                    '/system/main-page' => [
                        'name' => 'Main Page',
                    ],
                    '/system/main-page/vfx' => [
                        'name' => 'Video Effects',
                    ],
                    '/system/main-page/sfx' => [
                        'name' => 'Sound Effects',
                    ],
                ],
            ],
            'blog' => [
                'name' => 'Blog',
                'icon' => 'body-text',
                'children' => [
                    '/system/blog' => [
                        'name' => 'Posts',
                    ],
                    '/system/blog/categories' => [
                        'name' => 'Categories',
                    ],
                    '/system/blog/authors' => [
                        'name' => 'Authors',
                    ],
                    '/system/blog/social-links' => [
                        'name' => 'Social Links',
                    ],
                ],
            ],
            'promocodes' => [
                'name' => 'Promocodes',
                'route' => '/system/promocodes',
                'icon' => 'percent',
            ],
            'misc' => [
                'name' => 'Miscellaneous',
                'icon' => 'three-dots',
                'children' => [
                    '/system/misc/robots' => ['name' => 'Robots'],
                    '/system/misc/statistics' => ['name' => 'Exports'],
                    '/system/misc/orders' => ['name' => 'Orders'],
                    '/system/misc/subscriptions' => ['name' => 'Subscriptions'],
                ],
            ],
            'analytics' => [
                'name' => 'Analytics',
                'icon' => 'fingerprint',
                'route' => '/system/analytics/refund',
            ],
            'packs' => [
                'name' => 'Packs',
                'icon' => 'collection',
                'children' => [
                    '/system/packs' => [
                        'name' => 'System',
                    ],
                    '/system/packs/personal' => [
                        'name' => 'Personal',
                    ],
                ],
            ],
            'core' => [
                'name' => 'Core',
                'icon' => 'calendar2-heart',
                'children' => [
                    '/system/core' => [
                        'name' => 'Index',
                    ],
                    '/system/core/defaults' => [
                        'name' => 'Defaults',
                    ]
                ]
            ],
            'content' => [
                'name' => 'Content',
                'icon' => 'bookshelf',
                'children' => [
                    '/system/content/licenses' => [
                        'name' => 'Licenses',
                    ],
                    '/system/content/tracks' => [
                        'name' => 'Tracks',
                    ],
                    '/system/content/tags' => [
                        'name' => 'Tags',
                    ],
                    '/system/content/sound-effects' => [
                        'name' => 'Sound Effects',
                    ],
                ],
            ],
            'video-effects' => [
                'name' => 'Video-Effects',
                'icon' => 'camera-reels',
                'children' => [
                    '/system/video-effects' => [
                        'name' => 'Dashboard',
                    ],
                    '/system/video-effects/list' => [
                        'name' => 'All'
                    ],
                    '/system/video-effects/submissions' => [
                        'name' => 'Submissions',
                    ],
                    '/system/video-effects/tags' => [
                        'name' => 'Tags',
                    ]
                ]
            ]
        ];

        $newLinks = [
            'multi' => [],
            'single' => [],
        ];

        foreach ($links as $link => $info) {
            if (!isset($info['children'])) {
                continue;
            }
            $children = $info['children'];
            ksort($children);
            $links[$link]['children'] = $children;
        }

        foreach ($links as $link => $info) {
            $link = "system/$link";

            if (isset($info['children'])) {
                $newLinks['multi'][$link] = $info;
                continue;
            }

            $newLinks['single'][$link] = $info;
        }
        unset($links);

        View::share('system_menu', $newLinks);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
