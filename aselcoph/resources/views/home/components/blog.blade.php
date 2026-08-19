<section class="blog-area overflow-hidden bg-white space-extra2" id="blog-sec">
    <div class="container">
        <div class="title-area text-center">
            <span class="sub-title sub-title2">News & Updates</span>
            <h2 class="sec-title">Latest News, Advisories & Community Stories</h2>
        </div>

        <div class="row gy-4">
            <!-- Repeat this block for each post (up to 6 total) -->

            @foreach (App\Models\postModel::where('isDeleted', 0)->latest()->get() as $blog)
                <div class="col-md-6 col-lg-4">
                    <div class="blog-box h-100">
                        <div class="blog-img">
                            <a href="/blog/{{ $blog->post_id }}/{{ Str::slug($blog->post_title) }}">
                                <img src="{{ asset($blog->post_thumbnail) }}" alt="{{ $blog->post_title }}">
                            </a>
                        </div>
                        <div class="box-content">
                            <div class="blog-meta">
                                <a href="/blog/{{ $blog->post_id }}/{{ Str::slug($blog->post_title) }}"><i
                                        class="far fa-calendar"></i>{{ date_format($blog->created_at, 'd M, Y') }}</a>
                                <a href="/blog/{{ $blog->post_id }}/{{ Str::slug($blog->post_title) }}"><i
                                        class="fa-regular fa-clock"></i>{{ $blog->created_at->diffForHumans() }}</a>
                            </div>
                            <h3 class="box-title">
                                <a
                                    href="/blog/{{ $blog->post_id }}/{{ Str::slug($blog->post_title) }}">{{ $blog->post_title }}</a>
                            </h3>
                            <a href="/blog/{{ $blog->post_id }}/{{ Str::slug($blog->post_title) }}"
                                class="line-btn">Read Details<i class="fa-solid fa-angles-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


<style>
    .blog-box {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border: 1px solid #eee;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* Fix height for image section */
    .blog-img {
        height: 200px;
        overflow: hidden;
    }

    .blog-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Ensures image fills without distortion */
        display: block;
    }

    .box-content {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px;
    }

    .box-title {
        flex-grow: 1;
        margin-bottom: 20px;
    }
</style>
