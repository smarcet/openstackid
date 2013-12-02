<?php

namespace services;

use auth\OpenIdUser;
use Log;
use openid\services\IUserService;
use Exception;

class UserService implements IUserService
{

    public function associateUser($id, $proposed_username)
    {
        try {
            $user = OpenIdUser::where('id', '=', $id)->first();
            if (!is_null($user)) {
                \DB::transaction(function () use ($id, $proposed_username) {
                    $done = false;
                    $fragment_nbr = 1;
                    do {
                        $old_user = \DB::table('openid_users')->where('identifier', '=', $proposed_username)->first();
                        if (is_null($old_user)) {
                            \DB::table('openid_users')->where('id', '=', $id)->update(array('identifier' => $proposed_username));
                            $done = true;
                        } else {
                            $proposed_username = $proposed_username . "." . $fragment_nbr;
                            $fragment_nbr++;
                        }

                    } while (!$done);
                    return $proposed_username;
                });
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return false;
    }

    public function updateLastLoginDate($identifier)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                \DB::transaction(function () use ($identifier) {
                    \DB::table('openid_users')->where('id', '=', $identifier)->update(array('last_login_date' => gmdate("Y-m-d H:i:s", time())));
                });
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function updateFailedLoginAttempts($identifier)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                $attempts = $user->login_failed_attempt;
                ++$attempts;
                \DB::transaction(function () use ($identifier, $attempts) {
                    \DB::table('openid_users')->where('id', '=', $identifier)->update(array('login_failed_attempt' => $attempts));
                });
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function lockUser($identifier)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                \DB::transaction(function () use ($identifier) {
                    \DB::table('openid_users')->where('id', '=', $identifier)->update(array('lock' => 1));
                });
                Log::warning(sprintf("User %d locked ", $identifier));
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function unlockUser($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if (!is_null($user)) {
            \DB::transaction(function () use ($identifier) {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('lock' => 0));
            });
        }
    }

    public function activateUser($identifier)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                \DB::transaction(function () use ($identifier) {
                    \DB::table('openid_users')->where('id', '=', $identifier)->update(array('active' => 1));
                });
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function deActivateUser($identifier)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                \DB::transaction(function () use ($identifier) {
                    \DB::table('openid_users')->where('id', '=', $identifier)->update(array('active' => 0));
                });
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email)
    {
        try {
            $user = OpenIdUser::where('id', '=', $identifier)->first();
            if (!is_null($user)) {
                $user->public_profile_show_photo = $show_pic;
                $user->public_profile_show_fullname = $show_full_name;
                $user->public_profile_show_email = $show_email;
                $user->Save();
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}