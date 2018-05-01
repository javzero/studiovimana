<script>

    //$('.UpdateCustomerAvatar').click(function(){
    //   
    //});

    $(document).ready(function() {
        $('#UpdateCustomerAvatarBtn').click(function(){
            $('#CustomerAvatarInput').click();
        });       
    });
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('.Image-Container').attr('src', e.target.result);
            }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#CustomerAvatarInput").change(function(){
        readURL(this);
        $('#ConfirmChange').removeClass('Hidden');
    });
    
    $('.CheckImg').on('error', function(){
        var defaultImg = "{{ asset('images/users/default.jpg') }}"
        $(this).attr('src', defaultImg);
    });
    // Laravel Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
			


</script>