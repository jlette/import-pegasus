<?php

use App\Helper\AssetHelper;
?>
<header class="header">
    <nav>
        <ul class="header__nav">
            <li><a href="<?= BASE_URL ?>">
                    <img class="header__nav__logo" src="<?= AssetHelper::generateImgUrl('logo/logo-ens-psl.svg') ?>"
                        alt="Logo ENS PSL" />
                </a>
            </li>
            <li><span class="header__nav__title">Normalisateur des admissions</span></li>
        </ul>
    </nav>
</header>