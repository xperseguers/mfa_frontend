<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\MfaFrontend\ViewHelpers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Causal\MfaFrontend\Domain\Immutable\TotpSecret;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class QrImageViewHelper extends AbstractTagBasedViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    protected $tagName = 'img';

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('secret', TotpSecret::class, 'TOTP secret');
        $this->registerArgument('alt', 'string', 'Specifies an alternate text for the image', false, 'QR code');
        $this->registerArgument('size', 'int', 'width/height of the QR code', false, 200);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     */
    public function render()
    {
        /** @var TotpSecret $totpSecret */
        $totpSecret = $this->arguments['secret'];
        $alt = $this->arguments['alt'];
        $size = (int)$this->arguments['size'];

        if ($totpSecret === null) {
            throw new Exception('You must specify a TotpSecret object.', 1697747858);
        }

        $qrCodeRenderer = new ImageRenderer(
            new RendererStyle($size, 4),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($qrCodeRenderer))->writeString($totpSecret->getUri());
        $imageUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $this->tag->addAttribute('src', $imageUri);
        $this->tag->addAttribute('width', $size);
        $this->tag->addAttribute('alt', $alt);

        return $this->tag->render();
    }
}
