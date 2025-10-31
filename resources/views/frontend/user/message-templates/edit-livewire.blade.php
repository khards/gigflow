<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    <h2>Message Template Editor</h2>
                </x-slot>

                <x-slot name="body">
                    <p>Message Type: <b>{{ $type }}</b></p>
                    <p><a target='_preview' href="{{ route('frontend.user.message-template.preview', $mailTemplate->id)}}">Preview</a></p>
                    <p>Message Subject: <input class="form-control" wire:model="mailTemplate.subject" id="subject"/></p>

                    <div wire:ignore>
                        <p>HTML Content: <textarea wire:model="mailTemplate.html_template" id="html_template"></textarea></p>
                    </div>

                    <p>Text Content: <textarea class="form-control" wire:model="mailTemplate.text_template" rows="5"></textarea></p>

                </x-slot>
            </x-frontend.card>
        </div><!--col-md-10-->
    </div><!--row-->

    <div class="row justify-content-center">
        <div class="col-md-12">
            <h3>Tags:</h3>
            <pre>
            <?php
                $vars = $mailTemplate->getVariables();
                echo var_export($vars, true);
            ?>
            </pre>
        </div>
    </div>

    <div wire:ignore>
        <?php
//        $order = \App\Domains\Order\Order::first();
//
//        $bc = new \App\Domains\Email\Mailables\Booking\BookingConfirmation($order->user, $order);
//        echo "<div>" . htmlspecialchars_decode($bc->getRendered()) . "</div>";
        //echo $bc->getHtmlLayout();


        //getMailTemplate

        //Mail::to($order->user->email)->send($bc);
        ?>
    </div>
</div><!--container-->

@push('scripts')

@endpush

@push('after-scripts')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
<?php
/*
  *     <script src="https://cdn.tiny.cloud/1/eeb85yktdtzl0kbm145gr54m734gq66gj5psgfy8whdg28t6/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  */
  ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.1.2/tinymce.min.js"></script>

    <script>
        tinyref = null;

        // document.addEventListener('livewire:load', function () {
        //     alert("Loaded");
        // })

        document.addEventListener('template-saved', function (data) {
            //console.log(data.detail.html_template);
       //     tiny_init();
        })

        function tiny_init() {
            var old_editor_config = {

                plugins: 'print preview paste importcss searchreplace ' +
                    'autolink autosave save directionality code visualblocks ' +
                    'visualchars fullscreen image link media template codesample ' +
                    'table charmap hr pagebreak nonbreaking anchor toc insertdatetime ' +
                    'advlist lists wordcount imagetools textpattern noneditable ' +
                    'help charmap quickbars emoticons',
                imagetools_cors_hosts: ['picsum.photos'],
                menubar: 'file edit view insert format tools table help',
                toolbar: 'undo redo | bold italic underline strikethrough | ' +
                    'fontselect fontsizeselect formatselect | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'outdent indent |  ' +
                    'numlist bullist | ' +
                    'forecolor backcolor removeformat | ' +
                    'pagebreak | charmap emoticons | ' +
                    'fullscreen  preview save print | ' +
                    'insertfile image media template link anchor codesample | ltr rtl',
                toolbar_sticky: true,

                convert_urls: false,
                allow_script_urls: true,
                relative_urls : false,
                remove_script_host : false,


                file_picker_callback : function(callback, value, meta) {
                    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                    var y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

                    //var cmsURL = editor_config.path_absolute + 'filemanager/embedded?editor=' + meta.fieldname;
                    var cmsURL = "/" + 'filemanager/embedded?editor=' + meta.fieldname;

                    if (meta.filetype == 'image') {
                        cmsURL = cmsURL + "&type=Images";
                    } else {
                        cmsURL = cmsURL + "&type=Files";
                    }

                    tinyMCE.activeEditor.windowManager.openUrl({
                        url: cmsURL,
                        title: 'Filemanager',
                        width: x * 0.8,
                        height: y * 0.8,
                        resizable: "yes",
                        close_previous: "no",
                        onMessage: (api, message) => {
                            callback(message.content);
                        }
                    });
                },

//                plugins: 'code advlist lists',
                selector: '#html_template',
                forced_root_block: false,
                setup: function (editor) {
                    editor.on('init change', function () {
                        editor.save();
                    });
                    editor.on('change', function (e) {
                        @this.set('mailTemplate.html_template', editor.getContent());
                    });
                }
            };

            // var editor_config = {
            //     path_absolute : "/",
            //     selector: 'textarea.my-editor',
            //     relative_urls: false,
            //     plugins: [
            //         "advlist autolink lists link image charmap print preview hr anchor pagebreak",
            //         "searchreplace wordcount visualblocks visualchars code fullscreen",
            //         "insertdatetime media nonbreaking save table directionality",
            //         "emoticons template paste textpattern"
            //     ],
            //     // plugins: 'print preview paste importcss searchreplace ' +
            //     //     'autolink autosave save directionality code visualblocks ' +
            //     //     'visualchars fullscreen image link media template codesample ' +
            //     //     'table charmap hr pagebreak nonbreaking anchor toc insertdatetime ' +
            //     //     'advlist lists wordcount imagetools textpattern noneditable ' +
            //     //     'help charmap quickbars emoticons',
            //
            //     toolbar:    "insertfile undo redo | styleselect | bold italic | " +
            //                 "alignleft aligncenter alignright alignjustify | " +
            //                 "bullist numlist outdent indent | link image media",
            //
            //     convert_urls: false,
            //     allow_script_urls: true,
            //     relative_urls : false,
            //     remove_script_host : false,
            //
            //     file_picker_callback : function(callback, value, meta) {
            //         var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
            //         var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
            //
            //         var cmsURL = editor_config.path_absolute + 'filemanager/embedded?editor=' + meta.fieldname;
            //         if (meta.filetype == 'image') {
            //             cmsURL = cmsURL + "&type=Images";
            //         } else {
            //             cmsURL = cmsURL + "&type=Files";
            //         }
            //
            //         tinyMCE.activeEditor.windowManager.openUrl({
            //             url : cmsURL,
            //             title : 'Filemanager',
            //             width : x * 0.8,
            //             height : y * 0.8,
            //             resizable : "yes",
            //             close_previous : "no",
            //             onMessage: (api, message) => {
            //                 callback(message.content);
            //             }
            //         });
            //     }
            // };

            tinyref = tinymce.init(old_editor_config);
        }
        tiny_init();
    </script>
@endpush
