<?php

namespace App\Models;

/**
 * Authorization marker for the dashboard module (no database table).
 *
 * Business rules:
 * - Each authenticated role has a dedicated dashboard presentation service.
 * - Statistics data is always sourced from ReportStatisticsService, not Dashboard models.
 *
 * Related modules: DashboardPolicy, DashboardController, DashboardService.
 */
class Dashboard
{
}
