<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the block_itp Moodle code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    block_itp
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024020801.02;
$plugin->requires = 2023100903.11;
$plugin->component = 'block_itp';


$plugin->release='1.0';
$plugin->maturity= MATURITY_STABLE;
