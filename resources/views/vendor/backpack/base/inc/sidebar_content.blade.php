<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
{{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> <b>{{ trans('backpack::base.dashboard') }}</b></a></li>--}}

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('brand') }}'><i class='nav-icon la la-code-branch'></i> <b>Brands</b></a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon la la-list-ul'></i> <b>Categories</b></a></li>

<li class="nav-item nav-dropdown open">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> <b>Products Data</b></a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('product') }}'><i class='nav-icon la la-sitemap'></i> Products</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('productcolor') }}'><i class='nav-icon la la-brush'></i> Product Colors</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('productsize') }}'><i class='nav-icon la la-arrows-alt-h'></i> Product Sizes</a></li>
    </ul>
</li>

<li class="nav-item nav-dropdown open">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> <b>Products options</b></a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('color') }}'><i class='nav-icon la la-brush'></i> Colors</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('size') }}'><i class='nav-icon la la-arrows-alt-h'></i> Sizes</a></li>
    </ul>
</li>

<li class="nav-item nav-dropdown open">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> <b>Rules</b></a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipbrandrule') }}'><i class='nav-icon la la-question'></i> Skip Brand</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipproducttitlerule') }}'><i class='nav-icon la la-question'></i> Skip Product Title</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipsddiscountrule') }}'><i class='nav-icon la la-question'></i> Skip SD Discount</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipsdpricerule') }}'><i class='nav-icon la la-question'></i> Skip SD Price</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipproductrule') }}'><i class='nav-icon la la-question'></i> Skip Product SKU</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipcategoryrule') }}'><i class='nav-icon la la-question'></i> Skip Category</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('skipsdsizerule') }}'><i class='nav-icon la la-question'></i> Skip Size</a></li>
    </ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('pricingmodel') }}'><i class='nav-icon la la-table'></i> <b>Pricing Models</b></a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('translate') }}'><i class='nav-icon la la-language'></i> Translates</a></li>
{{--<li class='nav-item'><a class='nav-link' href='{{ backpack_url('colorimage') }}'><i class='nav-icon la la-question'></i> Color Images</a></li>--}}
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('cscartsetting') }}'><i class='nav-icon la la-question'></i> CsCart Settings</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('currency') }}'><i class='nav-icon la la-question'></i> Currencies</a></li>
