<?php

namespace App\Console\Commands\Synchronisation;

use App\Models\User;
use App\Models\Track;
use App\Models\Images;
use App\Models\Libs\Role;
use App\Models\SystemAuthor;
use App\Services\MetaService;
use App\Models\Authors\Author;
use App\Models\Structure\Meta;
use Illuminate\Console\Command;
use App\Services\UserRoleService;
use App\Services\Authors\AuthorProfileService;

class SyncCurrentAuthors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:current-authors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HARDCODE (almost) Syncs System Authors, creates their profiles, and links to exising tracks';
    /**
     * @var AuthorProfileService
     */
    private AuthorProfileService $profileService;
    /**
     * @var UserRoleService
     */
    private UserRoleService $roleService;

    /**
     * Create a new command instance.
     *
     * @param AuthorProfileService $profileService
     * @param UserRoleService $roleService
     */
    public function __construct(AuthorProfileService $profileService, UserRoleService $roleService)
    {
        parent::__construct();
        $this->profileService = $profileService;
        $this->roleService = $roleService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         *    paulcarvine@gmail.com    x-guitar@yandex.ru    prigidabreven@gmail.com    Pvlbutorin@gmail.com    soundrise00@gmail.com    denismaksimov155@gmail.com    cosmonkey@list.ru    runmoodmode@gmail.com    tunes.diamond@gmail.com
         * Profiles    Paul Keane    Alex Stoner    Prigida    Jam Morgan    Mark July    Max Tune    Mike Cosmo    Pecan Pie    Diamond Tunes
         * Abbynoise                Firestorm    Yuki
         */
        $systemAuthors = SystemAuthor::all();

        foreach ($systemAuthors as $systemAuthor) {
            $author = $this->findOrCreateAuthor($systemAuthor);

            if (!$author) {
                $this->output->text("can't find email for author: {$systemAuthor->name}");
                continue;
            }

            $profile = $author->profiles->where('author_id', $author->id)->where('name', $systemAuthor->name)->first();

            $profileNew = false;
            if ($profile) {
                $this->output->text("profile for system author exists");
            } else {
                $profile = $this->profileService->createNewProfile($author, $systemAuthor->name, $systemAuthor->description, true);
                $profileNew = true;
            }

            $imagesMorphData = [
                'type' => "App\Models\Author",
                'type_id' => $systemAuthor->id,
            ];

            $images = Images::where($imagesMorphData)->get();
            foreach ($images as $image) {
                $data = $image->toArray();
                $data['type'] = $profile->getMorphClass();
                $data['type_id'] = $profile->id;

                Images::create($data);
            }

            $metaMorphData = [
                'type' => 'Meta-Authors',
                'type_id' => $systemAuthor->id,
            ];

            $metaService = resolve(MetaService::class);

            $meta = Meta::where($metaMorphData)->get();
            foreach ($meta as $item) {
                $data = array_merge([
                    'type' => $metaService->morphTypeKey(get_class_name($profile->getMorphClass())),
                    'type_id' => $profile->id
                ], [
                   'slug' => $item->slug,
                   'value' => $item->value,
                ]);

                Meta::create($data);
            }

            if (!$profileNew) {
                continue;
            }

            Track::where('author_id', $systemAuthor->id)->update([
                'author_profile_id' => $profile->id,
            ]);
        }
    }

    protected function findOrCreateAuthor(SystemAuthor $systemAuthor)
    {
        $email = $this->getUserEmailBySystemAuthorName($systemAuthor->name);

        if (!$email) {
            return null;
        }

        $author = Author::where('email', $email)->first();

        if ($author) {
            return $author;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email
            ]);
        }

        $this->roleService->assignRoleToUser($user, Role::ROLE_AUTHOR);

        return Author::find($user->id);
    }

    /**
     * @param string $authorName
     *
     * @return string|null
     */
    protected function getUserEmailBySystemAuthorName($authorName)
    {
        return match ($authorName) {
            'Paul Keane' => 'paulcarvine@gmail.com',
            'Alex Stoner', 'Abbynoise' => 'x-guitar@yandex.ru',
            'Prigida' => 'prigidabreven@gmail.com',
            'Jam Morgan' => 'Pvlbutorin@gmail.com',
            'Mark July' => 'soundrise00@gmail.com',
            'Max Tune', 'Firestorm' => 'denismaksimov155@gmail.com',
            'Mike Cosmo', 'Yuki' => 'cosmonkey@list.ru',
            'Pecan Pie' => 'runmoodmode@gmail.com',
            'Diamond Tunes' => 'tunes.diamond@gmail.com',
            default => 'freetaketones@gmail.com',
        };

    }
}
