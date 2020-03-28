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
use OCA\RansomwareDetection\Settings\PersonalSection;
use OCP\IURLGenerator;
use OCP\IL10N;
use Test\TestCase;

class PersonalSectionTest extends TestCase {

	/** @var PersonalSection */
	private $personalSection;

    /** @var IURLGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlGenerator;

    /** @var IL10N|\PHPUnit_Framework_MockObject_MockObject */
    protected $l;

    public function setUp(): void
    {
		parent::setUp();

        $this->urlGenerator = $this->getMockForAbstractClass( IURLGenerator::class, array(), '', FALSE, TRUE, TRUE, array( 'imagePath' ) );
        $this->l = $this->getMockForAbstractClass( IL10N::class, array(), '', FALSE, TRUE, TRUE, array( 't' ) );

		$this->personalSection = new PersonalSection($this->urlGenerator, $this->l);
	}

    public function testGetIcon() {
        $this->urlGenerator->expects($this->once())
            ->method('imagePath')
            ->with('ransomware_detection', 'app-dark.svg')
            ->willReturn('app-dark.svg');

        $this->assertSame('app-dark.svg', $this->personalSection->getIcon());
    }

    public function testGetID() {
		$this->assertSame(Application::APP_ID, $this->personalSection->getID());
	}

	public function testGetName() {
        $this->l->expects($this->once())
            ->method('t')
            ->with('Ransomware recovery')
            ->willReturn('Ransomware recovery');

		$this->assertSame('Ransomware recovery', $this->personalSection->getName());
	}

	public function testGetPriority() {
		$this->assertSame(15, $this->personalSection->getPriority());
	}
}
