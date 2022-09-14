<?php namespace tanglePHP\FaucetClient\Api\v1\Response;

use tanglePHP\Core\Models\AbstractApiResponse;
use tanglePHP\Core\Exception\Helper as HelperException;

/**
 * Class Info
 *
 * @package      tanglePHP\FaucetClient\Api\v1\Response
 * @author       Stefan Braun <stefan.braun@tanglePHP.com>
 * @copyright    Copyright (c) 2022, StefanBraun
 * @version      2022.09.05-1150
 */
final class Info extends AbstractApiResponse {
  /**
   * @var string
   */
  public string $address;
  /**
   * @var int
   */
  public int $balance;
  /**
   * @var string
   */
  public string $tokenName;
  /**
   * @var string
   */
  public string $bech32HRP;

  /**
   * @return void
   * @throws HelperException
   */
  protected function parse(): void {
    $this->defaultParse();
  }
}