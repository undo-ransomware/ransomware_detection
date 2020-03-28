<?php

/**
 * @copyright Copyright (c) 2018 Matthias Held <matthias.held@uni-konstanz.de>
 * @author Matthias Held <matthias.held@uni-konstanz.de>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection\tests\Unit\Settings;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Settings\Admin;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use Test\TestCase;

class AdminTest extends TestCase {

	/** @var Admin */
	private $admin;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    public function setUp(): void
    {
		parent::setUp();

        $this->config = $this->getMockForAbstractClass( IConfig::class, array(), '', FALSE, TRUE, TRUE, array( 'getAppValue' ) );

		$this->admin = new Admin($this->config);
	}

    public function dataGetForm()
    {
        return [
            ['suspicionLevel' => 2, 'minimumSequenceLength' => 5, 'expireDays' => 7, 'activeSuspicionLevel' => ['code' => 2, 'name' => 'Suspicious'], 'suspicionLevels' => [['code' => 1, 'name' => 'Maybe suspicious']]],
            ['suspicionLevel' => 1, 'minimumSequenceLength' => 5, 'expireDays' => 7, 'activeSuspicionLevel' => ['code' => 1, 'name' => 'Maybe suspicious'], 'suspicionLevels' => [['code' => 2, 'name' => 'Suspicious']]],
            ['suspicionLevel' => 3, 'minimumSequenceLength' => 5, 'expireDays' => 7, 'activeSuspicionLevel' => [], 'suspicionLevels' => [['code' => 1, 'name' => 'Maybe suspicious'], ['code' => 2, 'name' => 'Suspicious']]],
        ];
    }

    /**
     * @dataProvider dataGetForm
     *
     * @param int     $suspicionLevel
     * @param int     $minimumSequenceLength
     * @param int     $expireDays
     * @param array   $activeSuspicionLevel
     * @param array   $suspicionLevels
     */
	public function testGetForm($suspicionLevel, $minimumSequenceLength, $expireDays, $activeSuspicionLevel, $suspicionLevels) {

        $this->config->expects($this->any())
            ->method('getAppValue')
            ->withConsecutive([Application::APP_ID, 'suspicion_level', $this->anything()], [Application::APP_ID, 'minimum_sequence_length', $this->anything()], [Application::APP_ID, 'expire_days', $this->anything()])
            ->willReturnOnConsecutiveCalls($suspicionLevel, $minimumSequenceLength, $expireDays);

		$expected = new TemplateResponse(Application::APP_ID, 'admin',
                            ['minimum_sequence_length' => $minimumSequenceLength, 'active_suspicion_level' => $activeSuspicionLevel, 'suspicion_levels' => $suspicionLevels, 'expire_days' => $expireDays], '');

        $this->assertEquals($expected, $this->admin->getForm());
	}

	public function testGetSection() {
		$this->assertSame(Application::APP_ID, $this->admin->getSection());
	}

	public function testGetPriority() {
		$this->assertSame(1, $this->admin->getPriority());
	}
}
