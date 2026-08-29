@extends('layouts.app')

@section('title', 'Smart Search')
@section('page-title', 'Smart Search')

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- ========================= --}}
        {{-- Header --}}
        {{-- ========================= --}}

        <div class="mb-1">

            <h5 class="mb-0">
                <i class="bi bi-search-heart me-2"></i>
                Ask Smart Search
            </h5>

        </div>

        <p class="text-muted small mb-4">
            Ask questions about students, fees, rooms, complaints and hostel operations.
        </p>


        {{-- ========================= --}}
        {{-- Search Box --}}
        {{-- ========================= --}}

        <div class="position-relative">

            <form id="smartSearchForm">

                <div class="input-group">

                    <input
                        type="text"
                        id="smartSearchInput"
                        class="form-control"
                        placeholder="Type your question..."
                        autocomplete="off"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-search me-1"></i>
                        Search

                    </button>

                </div>

            </form>


            {{-- ========================= --}}
            {{-- Suggestions --}}
            {{-- ========================= --}}

            <div
                id="searchSuggestions"
                class="list-group position-absolute w-100 shadow-sm"
                style="
                    z-index: 1050;
                    display: none;
                    max-height: 320px;
                    overflow-y: auto;
                ">
            </div>

        </div>


        {{-- ========================= --}}
        {{-- Search Result --}}
        {{-- ========================= --}}

        <div id="searchResult" class="mt-4"></div>


        {{-- ========================= --}}
        {{-- Recent Searches --}}
        {{-- ========================= --}}

        <div
            id="historySection"
            class="mt-4 pt-3 border-top">

            <div class="d-flex justify-content-between align-items-center mb-2">

                <div class="small fw-semibold text-muted">

                    <i class="bi bi-clock-history me-1"></i>

                    Recent Searches

                </div>

                <button
                    type="button"
                    id="clearHistoryBtn"
                    class="btn btn-sm btn-outline-secondary">

                    <i class="bi bi-trash me-1"></i>

                    Clear History

                </button>

            </div>


            <div
                id="searchHistory"
                class="d-flex flex-wrap gap-2">
            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const input =
        document.getElementById('smartSearchInput');

    const suggestionsBox =
        document.getElementById('searchSuggestions');

    const form =
        document.getElementById('smartSearchForm');

    const resultBox =
        document.getElementById('searchResult');

    const historyBox =
        document.getElementById('searchHistory');

    const historySection =
        document.getElementById('historySection');

    const clearHistoryBtn =
        document.getElementById('clearHistoryBtn');


    /*
    |--------------------------------------------------------------------------
    | Suggested Questions
    |--------------------------------------------------------------------------
    */

    const questions = [

        // Fees / Payments

        "Which students have unpaid fees?",
        "Show students with outstanding dues",
        "Who has pending payments?",
        "Show overdue invoices",
        "Which invoices are overdue?",
        "Show unpaid students with due amount",
        "Which students have pending fees?",
        "Show students who owe money",
        "Show all unpaid students",
        "Show students with outstanding payments",


        // Rooms / Occupancy

        "Show room occupancy",
        "What is the current occupancy?",
        "Which hostels have vacant beds?",
        "Show available beds",
        "How many students are currently staying?",
        "Show hostel occupancy summary",
        "Which hostel has the highest occupancy?",
        "Show available beds by hostel",
        "Show total beds in each hostel",
        "Show occupied beds by hostel",


        // Complaints

        "Show open complaints",
        "Which complaints are still open?",
        "Show complaints by hostel",
        "Which hostel has the most complaints?",
        "Show pending complaints",
        "Show complaints currently in progress",
        "Show unresolved complaints",
        "How many complaints are open?",


        // Students

        "Show all students",
        "Show students with outstanding fees",
        "Which students are currently allocated rooms?",
        "Show students without room allocation",
        "How many students are currently staying?",
        "Show current students",
        "Show students with room allocation",


        // Hostel

        "Show current hostel occupancy",
        "Show hostel occupancy summary",
        "Show available beds by hostel",
        "Show total beds in each hostel",
        "Show occupied beds by hostel",
        "Which hostel has the highest occupancy?",


        // General

        "Show overdue fee invoices",
        "Show all unresolved complaints",
        "Show current occupancy",
        "Show vacant beds",
        "Show pending fees"

    ];


    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    const HISTORY_KEY =
        'hostel_smart_search_history';

    const MAX_HISTORY = 8;


    function getHistory() {

        try {

            return JSON.parse(
                localStorage.getItem(HISTORY_KEY)
            ) || [];

        } catch (error) {

            return [];

        }

    }


    function saveHistory(query) {

        let history = getHistory();


        // Remove duplicate

        history = history.filter(
            item =>
                item.toLowerCase() !== query.toLowerCase()
        );


        // Latest search first

        history.unshift(query);


        // Maximum 8

        history = history.slice(0, MAX_HISTORY);


        localStorage.setItem(
            HISTORY_KEY,
            JSON.stringify(history)
        );


        renderHistory();

    }


    function renderHistory() {

        const history = getHistory();

        historyBox.innerHTML = '';


        if (history.length === 0) {

            historySection.style.display = 'none';

            return;

        }


        historySection.style.display = 'block';


        history.forEach(query => {

            const button =
                document.createElement('button');


            button.type = 'button';


            button.className =
                'btn btn-sm btn-light border';


            button.innerHTML = `

                <i class="bi bi-clock-history me-1 text-muted"></i>

                ${escapeHtml(query)}

            `;


            /*
            |--------------------------------------------------------------------------
            | Click History = Search Automatically
            |--------------------------------------------------------------------------
            */

            button.addEventListener('click', function () {

                input.value = query;

                suggestionsBox.style.display = 'none';

                runSearch(query);

            });


            historyBox.appendChild(button);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Clear History
    |--------------------------------------------------------------------------
    */

    clearHistoryBtn.addEventListener('click', function () {

        localStorage.removeItem(HISTORY_KEY);

        renderHistory();

    });


    /*
    |--------------------------------------------------------------------------
    | Suggestions
    |--------------------------------------------------------------------------
    */

    function showSuggestions(value) {

        const query =
            value.trim().toLowerCase();


        suggestionsBox.innerHTML = '';


        if (!query) {

            suggestionsBox.style.display = 'none';

            return;

        }


        const matches = questions
            .filter(question =>
                question.toLowerCase().includes(query)
            )
            .slice(0, 10);


        if (matches.length === 0) {

            suggestionsBox.style.display = 'none';

            return;

        }


        matches.forEach(question => {

            const item =
                document.createElement('button');


            item.type = 'button';


            item.className =
                'list-group-item list-group-item-action text-start';


            item.innerHTML = `

                <i class="bi bi-lightbulb me-2 text-warning"></i>

                ${escapeHtml(question)}

            `;


            /*
            |--------------------------------------------------------------------------
            | Suggestion Click = Automatic Search
            |--------------------------------------------------------------------------
            */

            item.addEventListener('click', function () {

                input.value = question;

                suggestionsBox.style.display = 'none';

                runSearch(question);

            });


            suggestionsBox.appendChild(item);

        });


        suggestionsBox.style.display = 'block';

    }


    /*
    |--------------------------------------------------------------------------
    | Input
    |--------------------------------------------------------------------------
    */

    input.addEventListener('input', function () {

        showSuggestions(this.value);

    });


    /*
    |--------------------------------------------------------------------------
    | Focus
    |--------------------------------------------------------------------------
    */

    input.addEventListener('focus', function () {

        if (this.value.trim()) {

            showSuggestions(this.value);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Close Suggestions
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        if (
            !input.contains(event.target) &&
            !suggestionsBox.contains(event.target)
        ) {

            suggestionsBox.style.display = 'none';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Normal Search Button
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        event.preventDefault();


        const query =
            input.value.trim();


        if (!query) {

            return;

        }


        suggestionsBox.style.display = 'none';

        runSearch(query);

    });


    /*
    |--------------------------------------------------------------------------
    | Run Search
    |--------------------------------------------------------------------------
    */

    async function runSearch(query) {

        if (!query) {

            return;

        }


        saveHistory(query);


        suggestionsBox.style.display = 'none';


        resultBox.innerHTML = `

            <div class="text-center py-5">

                <div
                    class="spinner-border text-primary"
                    role="status">
                </div>

                <div class="small text-muted mt-2">

                    Searching...

                </div>

            </div>

        `;


        try {

            const response = await fetch(

                "{{ route('smart-search.search') }}",

                {

                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content

                    },

                    body: JSON.stringify({

                        query: query

                    })

                }

            );


            const data =
                await response.json();


            if (!response.ok) {

                throw new Error(
                    data.message ||
                    'Search failed.'
                );

            }


            renderResult(data);


        } catch (error) {

            resultBox.innerHTML = `

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-triangle me-2"></i>

                    ${escapeHtml(error.message)}

                </div>

            `;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Render Result
    |--------------------------------------------------------------------------
    */

    function renderResult(data) {

        let html = '';


        html += `

            <div class="alert alert-info">

                <i class="bi bi-info-circle me-2"></i>

                ${escapeHtml(data.summary ?? '')}

            </div>

        `;


        if (
            !data.rows ||
            data.rows.length === 0
        ) {

            html += `

                <div class="alert alert-secondary">

                    <i class="bi bi-inbox me-2"></i>

                    No matching records.

                </div>

            `;


            resultBox.innerHTML = html;

            return;

        }


        html += `

            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

        `;


        (data.columns || []).forEach(column => {

            html += `

                <th>
                    ${escapeHtml(column)}
                </th>

            `;

        });


        html += `

                        </tr>

                    </thead>

                    <tbody>

        `;


        data.rows.forEach(row => {

            html += `<tr>`;


            row.forEach(value => {

                html += `

                    <td>
                        ${escapeHtml(value ?? '-')}
                    </td>

                `;

            });


            html += `</tr>`;

        });


        html += `

                    </tbody>

                </table>

            </div>

        `;


        resultBox.innerHTML = html;

    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            String(value);

        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | Load History
    |--------------------------------------------------------------------------
    */

    renderHistory();

});

</script>

@endpush