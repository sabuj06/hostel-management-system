@extends('layouts.student')

@section('title', 'Ask About Rules')
@section('page-title', 'Ask About Hostel Rules & Policy')

@section('content')
<div class="alert alert-info small">
    <i class="bi bi-info-circle"></i> Ask anything about hostel rules, curfew, fees policy, or conduct — answers come directly from the official hostel documents.
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-3" id="qa-messages" style="min-height:400px; max-height:500px; overflow-y:auto;">
        <div class="text-center text-muted py-5" id="qa-placeholder">
            <i class="bi bi-journal-text fs-1"></i>
            <p class="mt-2 mb-0">Ask a question to get started, e.g. "What's the curfew time?" or "hostel theke koto din er jonno leave newa jay?"</p>
        </div>
    </div>
    <div class="card-footer bg-white">
        <form id="qaForm" class="d-flex gap-2">
            <input type="text" id="qa-input" class="form-control" placeholder="Type your question about hostel rules..." autocomplete="off">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Ask</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function appendQuestion(text) {
            $('#qa-placeholder').remove();
            $('#qa-messages').append(`
                <div class="d-flex justify-content-end mb-2">
                    <div class="bg-primary text-white rounded p-2 px-3" style="max-width:80%;">${$('<div>').text(text).html()}</div>
                </div>
            `);
            $('#qa-messages').scrollTop($('#qa-messages')[0].scrollHeight);
        }

        function appendAnswer(answer, sources) {
            let sourcesHtml = '';
            if (sources && sources.length) {
                sourcesHtml = `<div class="small text-muted mt-2"><i class="bi bi-file-earmark-text"></i> Source: ${sources.join(', ')}</div>`;
            }
            $('#qa-messages').append(`
                <div class="d-flex justify-content-start mb-3">
                    <div class="bg-light border rounded p-2 px-3" style="max-width:85%; white-space:pre-line;">
                        ${$('<div>').text(answer).html()}
                        ${sourcesHtml}
                    </div>
                </div>
            `);
            $('#qa-messages').scrollTop($('#qa-messages')[0].scrollHeight);
        }

        $('#qaForm').on('submit', function (e) {
            e.preventDefault();
            const $input = $('#qa-input');
            const question = $input.val().trim();
            if (!question) return;

            appendQuestion(question);
            $input.val('').prop('disabled', true);

            const $typing = $('<div class="d-flex justify-content-start mb-3" id="typing-indicator"><div class="bg-light border rounded p-2 px-3">Searching policy documents...</div></div>');
            $('#qa-messages').append($typing);
            $('#qa-messages').scrollTop($('#qa-messages')[0].scrollHeight);

            $.ajax({
                url: '{{ route('student-portal.policy-qa.ask') }}',
                method: 'POST',
                data: { question: question },
                success: function (res) {
                    $typing.remove();
                    appendAnswer(res.answer, res.sources);
                },
                error: function () {
                    $typing.remove();
                    appendAnswer('Sorry, something went wrong. Please try again.', []);
                },
                complete: function () {
                    $input.prop('disabled', false).focus();
                }
            });
        });
    });
</script>
@endpush