<section class="th-blog-wrapper blog-details space-top space-extra-bottom">
    <div class="container">
        <div class="row">
            <div class="col-xxl-12 col-lg-12">
                <div class="th-blog blog-single bg-white">
                    <div class="blog-img">
                        <img src="{{ asset($blog->post_thumbnail) }}" alt="{{ $blog->post_title }}">
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <a href="#"><i
                                    class="fa-regular fa-calendar"></i>{{ date_format($blog->created_at, 'd M, Y') }}</a>
                            <a href="#"><i
                                    class="fa-regular fa-clock"></i>{{ $blog->created_at->diffForHumans() }}</a>
                        </div>
                        <h2 class="blog-title pb-0 mb-0">{{ $blog->post_title }}</h2>

                        <link rel="stylesheet" href="/assets/libs/quill/quill.snow.css">
                        <link rel="stylesheet" href="/assets/libs/quill/quill.bubble.css">
                        <script src="/assets/libs/quill/quill.js"></script>

                        <div class="prose custom-description" style="border: none !important; width: 100% !important;">
                            <div id="blog-description" style="font-family: 'DM Sans', sans-serif !important">
                                {!! $blog->post_content !!}</div>
                            <script>
                                const quill = new Quill(`#blog-description`, {
                                    theme: "snow",
                                    readOnly: true, // disables editing
                                    modules: {
                                        toolbar: false // disables (hides) the ribbon
                                    }
                                });
                            </script>
                        </div>

                        @php
                            $attachments = json_decode($blog->post_attachment, true);
                        @endphp

                        @foreach ($attachments as $file)
                            <div class="mb-3 border rounded shadow-sm p-1 bg-white">
                                <iframe src="{{ asset($file) }}" width="100%" height="800px"
                                    class="border rounded"></iframe>
                            </div>
                        <hr>
                        @endforeach
                        @if (!empty($attachments) && is_array($attachments))
                            <div class="mt-6">
                                <h5><strong>Attachments</strong></h5>
                                <div class="overflow-auto">
                                    <table class="table-auto w-full mt-3 text-sm text-left border border-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-2 border">Filename</th>
                                                <th class="px-4 py-2 border">Date Uploaded</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attachments as $file)
                                                <tr>
                                                    <td class="px-4 py-2 border text-blue-600">
                                                        <a href="{{ asset($file) }}" target="_blank"
                                                            class="flex items-center hover:underline">
                                                            <img src="/v1/pdf.png" alt="PDF" style="height: 24px"
                                                                class="h-4 w-4 mx-2">
                                                            {{ basename($file) }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-2 border text-gray-600">
                                                        {{ \Carbon\Carbon::createFromTimestamp(filemtime(public_path($file)))->format('F d, Y h:i A') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                        <style>
                            .ql-container.ql-snow.ql-disabled {
                                border: none !important;
                                width: 100% !important;
                                padding: 0 !important;
                                background: transparent !important;
                                font-family: 'DM Sans', sans-serif !important
                            }

                            .ql-editor {
                                padding: 0 !important;
                                font-family: 'DM Sans', sans-serif !important;
                                margin: 0 0 18px 0 !important;
                                color: #5C6574 !important;
                                line-height: 1.75 !important;
                                font-size: 16px !important;
                            }

                            #blog-description img {
                                border-radius: 20px;
                                margin-top: 20px;
                                margin-bottom: 20px;
                            }

                            #blog-description h1,
                            #blog-description h2,
                            #blog-description h3 {
                                font-family: 'DM Sans', sans-serif !important;
                                margin-top: 20px;
                                margin-bottom: 20px;
                            }

                            #blog-description p {
                                padding: 0 !important;
                                font-family: 'DM Sans', sans-serif !important;
                                color: #3c424b !important;
                                line-height: 1.75 !important;
                                font-size: 16px !important;
                                margin-top: 20px !important;
                                margin-bottom: 20px !important;
                            }

                            #blog-description ul,
                            #blog-description li {
                                font-family: 'DM Sans', sans-serif !important;
                                color: #3c424b !important;
                                margin-top: 20px !important;
                                margin-bottom: 20px !important;
                            }
                        </style>
                        <br>

                    </div>
                    <div class="share-links clearfix ">
                        <div class="row justify-content-between">
                            <div class="col-sm-auto text-xl-end">
                                <span class="share-links-title">Share:</span>
                                <ul class="social-links">
                                    <li><a href="https://facebook.com/" target="_blank"><i
                                                class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="https://twitter.com/" target="_blank"><i
                                                class="fab fa-twitter"></i></a></li>
                                    <li><a href="https://linkedin.com/" target="_blank"><i
                                                class="fab fa-linkedin-in"></i></a></li>
                                    <li><a href="https://instagram.com/" target="_blank"><i
                                                class="fab fa-instagram"></i></a></li>
                                </ul><!-- End Social Share -->
                            </div><!-- Share Links Area end -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-12 col-lg-12">
                <aside class="sidebar-area">
                    <div class="widget  bg-white ">
                        <h3 class="widget_title">Recent Posts</h3>
                        <div class="recent-post-wrap">
                            @foreach (App\Models\postModel::where('post_id', '<>', $id)->latest()->limit(3)->get() as $blog)
                                <div class="recent-post">
                                    <div class="media-img">
                                        <a href="blog-details.html"><img src="{{ asset($blog->post_thumbnail) }}"
                                                alt="{{ $blog->post_title }}"></a>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="post-title"><a class="text-inherit" href="#">
                                                {{ $blog->post_title }}
                                            </a></h4>
                                        <div class="recent-post-meta">
                                            <a href="blog.html">{{ date_format($blog->created_at, 'd M, Y') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
