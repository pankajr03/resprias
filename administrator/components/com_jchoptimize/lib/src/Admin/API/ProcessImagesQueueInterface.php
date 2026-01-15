<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\API;

interface ProcessImagesQueueInterface
{
    public function hasPendingImages(): bool;

    public function getFilePackages(float $startTime, float $maxExecutionTime): array;

    public function setCursor(?string $cursor): void;

    public function getCursor(): ?string;
}
