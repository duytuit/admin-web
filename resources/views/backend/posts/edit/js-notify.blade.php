<script>
    $('#customer_ids').select2({
        language: 'vi',
        ajax: {
            url: '{{ route("admin.ajax.ajaxcustomers") }}',
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
                            id: item.id,
                            text: item.full_name
                        });
                    }
                }
                return {
                    results: results
                };
            },
        }
    });
    $('#customer_ids').on('select2:select', function(e) {
        var data = e.params.data;
        console.log("Selected value is: "+$("#customer_ids").select2("val"));
    });
    $('#apartment_ids_selc').select2({
        language: 'vi',
        ajax: {
            url: '{{ route("admin.ajax.ajaxapartment") }}',
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
                            id: item.id,
                            text: item.name
                        });
                    }
                }

                return {
                    results: results
                };
            },
        }
    });
    $('#place_ids_selc').select2({
        language: 'vi',
        ajax: {
            url: '{{ route("admin.ajax.buildingplace") }}',
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
                            id: item.id,
                            text: item.name
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