<?php
/**
 * This file is part of the PhilKra/elastic-apm-php-agent library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @see https://github.com/philkra/elastic-apm-php-agent GitHub
 */

namespace PhilKra\Traces\Metadata;

use PhilKra\Traces\Trace;

/**
 * APM Metadata
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/metadata-api.html#metadata-user-schema
 * @version 6.7 (v2)
 */
class User implements Trace
{

    /** @var string * */
    private $id;

    /** @var string * */
    private $username;

    /** @var string * */
    private $email;

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Is any User Data registered?
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return null !== $this->id || null !== $this->username || null !== $this->email;
    }

    /**
     * Initialize the Object from an Array set
     *
     * @param array $arr
     */
    public function initFromArray(?array $arr): void
    {
        $this->id = ($arr['id']) ?? null;
        $this->username = ($arr['username']) ?? null;
        $this->email = ($arr['email']) ?? null;
    }

    /**
     * Serialize User Object
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [];
        if (null !== $this->id) {
            $payload['id'] = (string) $this->id;
        }
        if (null !== $this->username) {
            $payload['username'] = $this->username;
        }
        if (null !== $this->email) {
            $payload['email'] = $this->email;
        }

        return $payload;
    }
}
