@extends('site.layouts.layout-site')
@section('content')
    {{-- table --}}
    <div class="card-body">
        <div id="table-gridjs">
            <div role="complementary" class="gridjs gridjs-container" style="width: 100%;">
                <div class="gridjs-head">
                    <div class="gridjs-search"><input type="search" placeholder="Type a keyword..."
                            aria-label="Type a keyword..." class="gridjs-input gridjs-search-input"></div>
                </div>
                <div class="gridjs-wrapper" style="height: auto;">
                    <table role="grid" class="gridjs-table" style="height: auto;">
                        <thead class="gridjs-thead">
                            <tr class="gridjs-tr">
                                <th data-column-id="id" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 80px;">
                                    <div class="gridjs-th-content">ID</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="name" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 150px;">
                                    <div class="gridjs-th-content">Name</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="email" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 220px;">
                                    <div class="gridjs-th-content">Email</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="position" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 250px;">
                                    <div class="gridjs-th-content">Position</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="company" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 180px;">
                                    <div class="gridjs-th-content">Company</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="country" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 180px;">
                                    <div class="gridjs-th-content">Country</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                                <th data-column-id="actions" class="gridjs-th gridjs-th-sort" tabindex="0"
                                    style="width: 150px;">
                                    <div class="gridjs-th-content">Actions</div><button tabindex="-1"
                                        aria-label="Sort column ascending" title="Sort column ascending"
                                        class="gridjs-sort gridjs-sort-neutral"></button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="gridjs-tbody">
                            <tr class="gridjs-tr">
                                <td data-column-id="id" class="gridjs-td"><span><span class="fw-semibold">01</span></span>
                                </td>
                                <td data-column-id="name" class="gridjs-td">Jonathan</td>
                                <td data-column-id="email" class="gridjs-td"><span><a
                                            href="">jonathan@example.com</a></span></td>
                                <td data-column-id="position" class="gridjs-td">Senior Implementation Architect</td>
                                <td data-column-id="company" class="gridjs-td">Hauck Inc</td>
                                <td data-column-id="country" class="gridjs-td">Holy See</td>
                                <td data-column-id="actions" class="gridjs-td"><span><a href="#"
                                            class="text-reset text-decoration-underline">Details</a></span></td>
                            </tr>
                            <tr class="gridjs-tr">
                                <td data-column-id="id" class="gridjs-td"><span><span
                                            class="fw-semibold">02</span></span></td>
                                <td data-column-id="name" class="gridjs-td">Harold</td>
                                <td data-column-id="email" class="gridjs-td"><span><a
                                            href="">harold@example.com</a></span></td>
                                <td data-column-id="position" class="gridjs-td">Forward Creative Coordinator</td>
                                <td data-column-id="company" class="gridjs-td">Metz Inc</td>
                                <td data-column-id="country" class="gridjs-td">Iran</td>
                                <td data-column-id="actions" class="gridjs-td"><span><a href="#"
                                            class="text-reset text-decoration-underline">Details</a></span></td>
                            </tr>
                            <tr class="gridjs-tr">
                                <td data-column-id="id" class="gridjs-td"><span><span
                                            class="fw-semibold">03</span></span></td>
                                <td data-column-id="name" class="gridjs-td">Shannon</td>
                                <td data-column-id="email" class="gridjs-td"><span><a
                                            href="">shannon@example.com</a></span></td>
                                <td data-column-id="position" class="gridjs-td">Legacy Functionality Associate
                                </td>
                                <td data-column-id="company" class="gridjs-td">Zemlak Group</td>
                                <td data-column-id="country" class="gridjs-td">South Georgia</td>
                                <td data-column-id="actions" class="gridjs-td"><span><a href="#"
                                            class="text-reset text-decoration-underline">Details</a></span></td>
                            </tr>
                            <tr class="gridjs-tr">
                                <td data-column-id="id" class="gridjs-td"><span><span
                                            class="fw-semibold">04</span></span></td>
                                <td data-column-id="name" class="gridjs-td">Robert</td>
                                <td data-column-id="email" class="gridjs-td"><span><a
                                            href="">robert@example.com</a></span></td>
                                <td data-column-id="position" class="gridjs-td">Product Accounts Technician</td>
                                <td data-column-id="company" class="gridjs-td">Hoeger</td>
                                <td data-column-id="country" class="gridjs-td">San Marino</td>
                                <td data-column-id="actions" class="gridjs-td"><span><a href="#"
                                            class="text-reset text-decoration-underline">Details</a></span></td>
                            </tr>
                            <tr class="gridjs-tr">
                                <td data-column-id="id" class="gridjs-td"><span><span
                                            class="fw-semibold">05</span></span></td>
                                <td data-column-id="name" class="gridjs-td">Noel</td>
                                <td data-column-id="email" class="gridjs-td"><span><a
                                            href="">noel@example.com</a></span></td>
                                <td data-column-id="position" class="gridjs-td">Customer Data Director</td>
                                <td data-column-id="company" class="gridjs-td">Howell - Rippin</td>
                                <td data-column-id="country" class="gridjs-td">Germany</td>
                                <td data-column-id="actions" class="gridjs-td"><span><a href="#"
                                            class="text-reset text-decoration-underline">Details</a></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="gridjs-footer">
                    <div class="gridjs-pagination">
                        <div role="status" aria-live="polite" class="gridjs-summary" title="Page 1 of 2">
                            Showing <b>1</b> to <b>5</b> of <b>10</b> results</div>
                        <div class="gridjs-pages"><button tabindex="0" role="button" disabled="" title="Previous"
                                aria-label="Previous" class="">Previous</button><button tabindex="0"
                                role="button" class="gridjs-currentPage" title="Page 1"
                                aria-label="Page 1">1</button><button tabindex="0" role="button" class=""
                                title="Page 2" aria-label="Page 2">2</button><button tabindex="0" role="button"
                                title="Next" aria-label="Next" class="">Next</button></div>
                    </div>
                </div>
                <div id="gridjs-temp" class="gridjs-temp"></div>
            </div>
        </div>
    </div>
    {{-- table --}}
@endsection
