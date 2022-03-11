<?php
namespace App\Controllers;

use App\Helpers\ErrorHandler;
use App\Helpers\Misc;
use App\Helpers\RSS;
use App\Models\FeedTemplate;

class UserController {
    static public function get(string $username) {
        $cursor = Misc::getCursor();
        $api = Misc::api();
        $feed = $api->getUserFeed($username, $cursor);
        if ($feed->meta->success) {
            if ($feed->info->detail->privateAccount) {
                http_response_code(403);
                echo 'Private account detected! Not supported';
                exit;
            }
            $latte = Misc::latte();
            $latte->render(Misc::getView('user'), new FeedTemplate($feed->info->detail->user->nickname, $feed));
        } else {
            ErrorHandler::show($feed->meta);
        }
    }

    static public function rss(string $username) {
        $api = Misc::api();
        $feed = $api->getUserFeed($username);
        if ($feed->meta->success) {
            $feed = RSS::build('/@'.$username, $feed->info->detail->user->nickname, $feed->info->detail->user->signature, $feed->items);
            // Setup headers
            RSS::setHeaders('user.rss');
            echo $feed;
        }
    }
}
