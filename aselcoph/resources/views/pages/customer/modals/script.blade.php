 <script>
        // Toggle to search UI
        $('#link-account').on('click', function() {
            $('#client_form').hide();
            $('#link-ui').removeClass('hidden');

            $('#PopupInfo #create-account').hide();
            $('#PopupInfo #link-account').hide();
            $('#PopupInfo #check-status').hide();
        });

        // Cancel back to form
        $('#cancel_link').on('click', function() {
            $('#link-ui').addClass('hidden');
            $('#client_form').show();
            $('#search_result, #user_match, #no_match').addClass('hidden');
            $('#PopupInfo #create-account').show();
            $('#PopupInfo #link-account').show();
            $('#PopupInfo #check-status').show();
        });

        // Search for user by email
        $('#search_user').on('click', function() {
            const email = $('#search_email').val();

            if (!email) return Swal.fire('Please enter an email.');

            $.post("{{ route('user.search') }}", {
                _token: '{{ csrf_token() }}',
                email: email
            }, function(res) {
                $('#search_result').removeClass('hidden');
                $('#result_email').text(email);

                if (res.user) {
                    $('#user_match').removeClass('hidden');
                    $('#no_match').addClass('hidden');
                    $('#found_name').text(res.user.name);
                    $('#found_contact').text(res.user.contact_no);
                    $('#found_user_id').val(res.user.id);
                } else {
                    $('#user_match').addClass('hidden');
                    $('#no_match').removeClass('hidden');
                }
            });
        });

        // Confirm link to existing user
        $('#confirm_link').on('click', function() {
            const user_id = $('#found_user_id').val();
            const account_no = $('#d_account_no').val();

            const $btn = $(this);
            $btn.prop('disabled', true).html(
                '<span class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4 mr-1 inline-block"></span> Processing...'
            );

            if (!user_id) return;

            $.post("{{ route('account.link.existing') }}", {
                _token: '{{ csrf_token() }}',
                user_id: user_id,
                account_no: account_no
            }, function(res) {
                HSOverlay.close(document.querySelector('#PopupInfo'));

                Swal.fire({
                    icon: 'success',
                    title: 'Linked',
                    text: res.message,
                    showConfirmButton: false,
                    timer: 3000
                });

                setTimeout(() => {
                    $('#clientTable').DataTable().ajax.reload();
                }, 3000);
            });
        });
    </script>


    <script>
        $('#create-account').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);
            $btn.prop('disabled', true).html(
                '<span class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4 mr-1 inline-block"></span> Processing...'
            );

            const formData = {
                account_no: $('#d_account_no').val(),
                consumer: $('#d_consumer').val(),
                email: $('#d_email').val(),
                contact: $('#d_mobile').val(),
                _token: $('input[name="_token"]').val()
            };

            $.post("{{ route('account.link.create') }}", formData, function(response) {

                // Close modal using HSOverlay (handles overlay too)
                const popup = document.querySelector('#PopupInfo');
                if (popup) {
                    HSOverlay.close(popup);
                }

                // Show success Swal
                Swal.fire({
                    icon: 'success',
                    title: 'Account Created',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 3000
                });

                // Reload table after 3 seconds
                setTimeout(() => {
                    $('#clientTable').DataTable().ajax.reload();
                }, 3000);

            }).fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Something went wrong.',
                });
            }).always(function() {
                $btn.prop('disabled', false).html('Create Account'); // reset button
            });
        });

        $('#save-changes').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-2"></span> Saving...');

            const formData = {
                account_no: $('#PopupInfo #d_account_no').val(),
                customer: $('#PopupInfo #d_consumer').val(),
                email: $('#PopupInfo #d_email').val(),
                contact: $('#PopupInfo #d_mobile').val(),
                _token: '{{ csrf_token() }}'
            };

            $.post("{{ route('account.update') }}", formData, function(response) {
                HSOverlay.close(document.querySelector('#PopupInfo'));

                Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 3000
                });

                setTimeout(() => {
                    $('#clientTable').DataTable().ajax.reload();
                }, 3000);

            }).fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: xhr.responseJSON?.message || 'Please check your input.'
                });
            }).always(function() {
                $btn.prop('disabled', false).html(
                    '<span class="bi bi-check-circle mx-2"></span> Save Changes');
            });
        });
    </script>

    <script>
        // Open password modal (example from row click)
        function openPasswordModal(userId) {
            $('#pw_user_id').val(userId);
            $('#pw_password').val('');
            $('#pw_password_confirmation').val('');
            HSOverlay.open(document.querySelector('#passwordModal'));
        }

        // Button loader toggle
        function toggleLoader(btnId, isLoading) {
            const $btn = $(`#${btnId}`);
            $btn.prop('disabled', isLoading);
            $btn.find('.default-text').toggleClass('hidden', isLoading);
            $btn.find('.loading-text').toggleClass('hidden', !isLoading);
        }

        // Handle password change
        $('#btn-change-password').on('click', function() {
            toggleLoader('btn-change-password', true);

            $.post("{{ route('account.password.change') }}", {
                user_id: $('#pw_user_id').val(),
                password: $('#pw_password').val(),
                password_confirmation: $('#pw_password_confirmation').val(),
                _token: '{{ csrf_token() }}'
            }, function(res) {
                Swal.fire('Success', res.message, 'success');
                $('#pw_password').val('')
                $('#pw_password_confirmation').val('')
                HSOverlay.close(document.querySelector('#passwordModal'));
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to change password', 'error');
            }).always(() => {
                toggleLoader('btn-change-password', false);
            });
        });

        // Handle password reset
        $('#btn-reset-password').on('click', function() {
            Swal.fire({
                title: 'Reset Password?',
                text: 'A new password will be generated and emailed to the user.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reset it!'
            }).then(result => {
                if (!result.isConfirmed) return;

                toggleLoader('btn-reset-password', true);

                $.post("{{ route('account.password.reset') }}", {
                    user_id: $('#pw_user_id').val(),
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    Swal.fire('Success', res.message, 'success');
                    HSOverlay.close(document.querySelector('#passwordModal'));
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reset password',
                        'error');
                }).always(() => {
                    toggleLoader('btn-reset-password', false);
                });
            });
        });
    </script>