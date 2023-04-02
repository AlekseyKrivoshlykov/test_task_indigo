<?php

namespace App\Service;

use App\Entity\PostgresPDO;
use DateTime;
use Exception;
use PDO;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class TelegramApiService
{
  protected $botName;
  protected $botToken;
  protected $redirectUri;

  public function __construct(protected ParameterBagInterface $params)
  {
      $this->botName = $this->params->get('bot_name');
      $this->botToken = $this->params->get('bot_token');
      $this->redirectUri = $this->params->get('redirect_uri');
  }
  
  /**
   *  
   * @return array
   */
  public function getParameters(): array
  {
      $telegramData = [];

      $telegramData['botName'] = $this->botName;
      $telegramData['botToken'] = $this->botToken;
      $telegramData['redirectUri'] = $this->redirectUri;

      return $telegramData;
  }

  /** 
   * @param $auth_data
   */ 
  public function checkTelegramAuth($auth_data) 
  {
      $check_hash = $auth_data['hash'];
      unset($auth_data['hash']);

      $data_check_arr = [];
      foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
      }

      sort($data_check_arr);
      $data_check_string = implode("\n", $data_check_arr);
      $secret_key = hash('sha256', $this->botToken, true);
      $hash = hash_hmac('sha256', $data_check_string, $secret_key);

      if (strcmp($hash, $check_hash) !== 0) {
        throw new Exception('Data is NOT from Telegram');
      }

      if ((time() - $auth_data['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
      }

        return $auth_data;
    }

    /**
     * @param @auth_data
     */
    public function saveTelegramUserData($auth_data): void
    {
      $auth_data_json = json_encode($auth_data);
      setcookie('tg_user', $auth_data_json);
    }

    public function getTelegramUserData($request) 
    {
      if ($request->cookies->get('tg_user') !== null) {
        $auth_data_json = urldecode($request->cookies->get('tg_user'));
        $auth_data = json_decode($auth_data_json, true);
        
        return $auth_data;
      }
      
      return false;
    }

    public function addTelegramUserIntoDb($userData)
    {
      $pdo = $this->getPDO();
      $user = $this->isHasUserTelegram($pdo, $userData);

      if (!$user) {
        $arr = [
          'telegram_id' => $userData['id'],
          'first_name'  => $userData['first_name'],
          'last_name'   => $userData['last_name'] ? $userData['last_name'] : null,
          'username'    => $userData['username'],
          'photo_url'   => $userData['photo_url'],
          'auth_date'   => gmdate('Y-m-d H:i:s', $userData['auth_date']),
        ];
  
        $sql = "INSERT INTO test_schema.user_data
          (telegram_id, first_name, last_name, username, photo_url, auth_date)
                VALUES
          (:telegram_id, :first_name, :last_name, :username, :photo_url, :auth_date)"
        ;

        $pdo->prepare($sql)->execute($arr);
      }
      
      $this->saveAuthHistory($pdo, $userData);
    }

    /**
     * @param $pdo
     * @param $userData
     * @return $user
     */
    public function isHasUserTelegram($pdo, $userData)
    {
      $query = $pdo->prepare("SELECT * FROM test_schema.user_data WHERE telegram_id = ?");
      $query->execute([$userData['id']]);
      $user = $query->fetch();

      return $user;
    }

    /**
     * @param $pdo
     * @param $userData
     * @return void
     */
    public function saveAuthHistory($pdo, $userData): void
    {
      // найти первую дату auth и если она больше 30 дней, то удалить все предыдущие записи
      $ar = [
        'telegram_id' => $userData['id'],
        'auth_date'   => gmdate('Y-m-d H:i:s', $userData['auth_date']),
      ];

      $quer = "INSERT INTO test_schema.user_auth_history
        (telegram_id, auth_date)
               VALUES
        (:telegram_id, :auth_date)"
      ;

      $pdo->prepare($quer)->execute($ar);
    }

    /**
     * @return PDO
     */
    public function getPDO(): PDO
    {
        $dsn = "pgsql:host=" . PostgresPDO::HOST . ";" .
                     "port=" . PostgresPDO::PORT . ";" .
                     "dbname=" . PostgresPDO::DATA_BASE_NAME . ";"
        ;

        return new PDO($dsn, PostgresPDO::DATA_BASE_USER, 
                       PostgresPDO::DATA_BASE_PASSWD, PostgresPDO::OPT);
    }

}
