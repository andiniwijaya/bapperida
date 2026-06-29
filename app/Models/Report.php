<?php

namespace App\Models;

/**
 * Authorization marker for the report module (no database table).
 *
 * Business rules:
 * - Used solely for Gate/Policy binding; reports aggregate data from letter modules.
 * - Report access mirrors letter module view permissions for authenticated roles.
 *
 * Related modules: ReportPolicy, ReportController, ReportStatisticsService.
 */
class Report
{
}
