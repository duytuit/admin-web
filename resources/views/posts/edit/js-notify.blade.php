<script>
    $('#customer_ids').select2({
        language: 'vi',
        minimumInputLength: 3,
        ajax: {
            url: '{{ route("admin.posts.ajax.customers") }}',
            dataType: 'json',
            data: function(params) {
                var query = {
                    keyword: params.term,
                }
                return query;
            },
            processResults: function(json, params) {
                var results = [];

                if (json.data) {
                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item.cb_id,
                            text: item.cb_name
                        });
                    }
                }

                return {
                    results: results
                };
            },
        }
    });
</script>