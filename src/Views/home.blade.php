@extends(Config::get('forum.master_file_extend'))

@section(Config::get('forum.yields.head'))
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="{{ url('/vendor/innoboxrr/forum/assets/vendor/spectrum/spectrum.css') }}" rel="stylesheet">
    <link href="{{ url('/vendor/innoboxrr/forum/assets/css/forum.css') }}" rel="stylesheet">
    @if($forum_editor == 'simplemde')
        <link href="{{ url('/vendor/innoboxrr/forum/assets/css/simplemde.min.css') }}" rel="stylesheet">
    @elseif($forum_editor == 'trumbowyg')
        <link href="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/ui/trumbowyg.css') }}" rel="stylesheet">
        <style>
            .trumbowyg-box, .trumbowyg-editor {
                margin: 0px auto;
            }
        </style>
    @endif
@stop

@section('content')
    <div id="forum" class="forum_home min-h-screen bg-gray-100">
        <div id="forum_hero" class="relative">
            <div id="forum_hero_dimmer" class="absolute inset-0 bg-black opacity-50"></div>
            <?php $headline_logo = Config::get('forum.headline_logo'); ?>
            @if(isset($headline_logo) && !empty($headline_logo))
                <h1 class="text-4xl font-bold text-center text-white">
					Foro de la Comunidad
				</h1>
            @else
                <h1 class="text-4xl font-bold text-center text-white">@lang('forum::intro.headline')</h1>
                <p class="text-center text-white">@lang('forum::intro.description')</p>
            @endif
        </div>

        @if(config('forum.errors'))
            @if(Session::has('forum_alert'))
                <div class="forum-alert bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mt-4">
                    <div class="container mx-auto">
                        <strong>
                            <i class="forum-alert-{{ Session::get('forum_alert_type') }}"></i>
                            {{ Config::get('forum.alert_messages.' . Session::get('forum_alert_type')) }}
                        </strong>
                        {{ Session::get('forum_alert') }}
                        <i class="forum-close cursor-pointer"></i>
                    </div>
                </div>
                <div class="forum-alert-spacer my-2"></div>
            @endif

            @if(count($errors) > 0)
                <div class="forum-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4">
                    <div class="container mx-auto">
                        <p>
                            <strong>
                                <i class="forum-alert-danger"></i> @lang('forum::alert.danger.title')
                            </strong>
                            @lang('forum::alert.danger.reason.errors')
                        </p>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endif

        <div class="container mx-auto px-4 forum_container my-8">
            <div class="flex flex-wrap -mx-2">

				<div class="w-full md:w-1/4 px-4">
					<!-- SIDEBAR -->
					<div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
						<!-- Botón de Nueva Discusión -->
						<button id="new_discussion_btn" class="w-full flex items-center justify-center gap-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-5 rounded-lg transition-all duration-200 shadow-md">
							<i class="forum-new text-lg"></i> 
							<span>@lang('forum::messages.discussion.new')</span>
						</button>
				
						<!-- Navegación -->
						<nav class="mt-8">
							<!-- Enlace "Todos los temas" -->
							<a href="/{{ Config::get('forum.routes.home') }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium text-md py-3 px-4 rounded-lg transition-all duration-200">
								<i class="forum-bubble text-2xl"></i> 
								<span>@lang('forum::messages.discussion.all')</span>
							</a>
				
							<!-- Categorías -->
							<div class="mt-4 space-y-1">
								@foreach($categories as $category)
									<a href="/{{ Config::get('forum.routes.home') }}/category/{{ $category->slug }}" class="flex items-center gap-3 p-1 rounded-lg transition-all duration-200 text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
										<span class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }}"></span>
										<span class="text-md font-medium">
											{{ $category->name }} ({{ $category->discussions->count() }})
										</span>
									</a>
								@endforeach
							</div>
						</nav>
					</div>
					<!-- END SIDEBAR -->
				</div>
				
                <div class="w-full md:w-3/4 px-2 right-column">
                    <div class="panel bg-white shadow rounded p-4">
                        <ul class="discussions divide-y divide-gray-200">
                            @foreach($discussions as $discussion)
                                <li class="py-4">
                                    <a class="discussion_list flex flex-col md:flex-row items-start" href="/{{ Config::get('forum.routes.home') }}/{{ Config::get('forum.routes.discussion') }}/{{ $discussion->category->slug }}/{{ $discussion->slug }}">
                                        <div class="forum_avatar mr-4">
                                            @if(Config::get('forum.user.avatar_image_database_field'))
                                                <?php $db_field = Config::get('forum.user.avatar_image_database_field'); ?>
                                                @if((substr($discussion->user->{$db_field}, 0, 7) == 'http://') || (substr($discussion->user->{$db_field}, 0, 8) == 'https://'))
                                                    <img src="{{ $discussion->user->{$db_field} }}" class="w-12 h-12 rounded-full">
                                                @else
                                                    <img src="{{ Config::get('forum.user.relative_url_to_image_assets') . $discussion->user->{$db_field} }}" class="w-12 h-12 rounded-full">
                                                @endif
                                            @else
                                                <span class="forum_avatar_circle inline-flex items-center justify-center w-12 h-12 rounded-full" style="background-color:#<?= \Innoboxrr\Forum\Helpers\ForumHelper::stringToColorCode($discussion->user->{Config::get('forum.user.database_field_with_user_name')}) ?>">
                                                    {{ strtoupper(substr($discussion->user->{Config::get('forum.user.database_field_with_user_name')}, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="forum_middle flex-1">
                                            <h3 class="forum_middle_title text-xl font-semibold">
                                                {{ $discussion->title }}
                                                <div class="forum_cat inline-block ml-2 px-2 py-1 text-sm font-medium rounded" style="background-color:{{ $discussion->category->color }}">
                                                    {{ $discussion->category->name }}
                                                </div>
                                            </h3>
                                            <span class="forum_middle_details text-gray-600 text-sm">
                                                @lang('forum::messages.discussion.posted_by')
                                                <span data-href="/user" class="font-medium">
                                                    {{ ucfirst($discussion->user->{Config::get('forum.user.database_field_with_user_name')}) }}
                                                </span>
                                                {{ \Carbon\Carbon::createFromTimeStamp(strtotime($discussion->created_at))->diffForHumans() }}
                                            </span>
                                            @if(isset($discussion->post[0]) && $discussion->post[0]->markdown)
                                                <?php $discussion_body = GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($discussion->post[0]->body); ?>
                                            @elseif(isset($discussion->post[0]))
                                                <?php $discussion_body = $discussion->post[0]->body; ?>
                                            @else
                                                <?php $discussion_body = ''; ?>
                                            @endif
                                            <p class="text-gray-700 mt-2">
                                                {{ substr(strip_tags($discussion_body), 0, 200) }}
                                                @if(strlen(strip_tags($discussion_body)) > 200)
                                                    {{ '...' }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="forum_right mt-2 md:mt-0 md:ml-4">
                                            <div class="forum_count text-center bg-gray-100 rounded-full w-12 h-12 flex items-center justify-center">
                                                <i class="forum-bubble mr-1"></i>
                                                {{ isset($discussion->postsCount[0]) ? $discussion->postsCount[0]->total : 0 }}
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div id="pagination" class="mt-4">
                        {{ $discussions->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div id="new_discussion" 
			class="fixed inset-0 bg-gray-900 bg-opacity-75 flex flex-col justify-end hidden">
			<div class="bg-white rounded-t-lg shadow-lg w-full p-6 relative overflow-y-auto max-h-screen">
				
				<div class="forum_loader dark absolute inset-0 flex items-center justify-center" 
					id="new_discussion_loader">
					<div class="loader"></div>
				</div>

				<form id="forum_form_editor" 
					action="/{{ Config::get('forum.routes.home') }}/{{ Config::get('forum.routes.discussion') }}" 
					method="POST">
					<!-- Encabezado (Título y Categoría) -->
					<div class="flex flex-wrap -mx-2 mb-4">
						<div class="w-full md:w-7/12 px-2">
							<input type="text"
								id="title"
								name="title"
								placeholder="@lang('forum::messages.editor.title')"
								value="{{ old('title') }}"
								class="border border-gray-300 rounded px-3 py-2 w-full">
						</div>
						<div class="w-full md:w-4/12 px-2">
							<select id="forum_category_id"
								name="forum_category_id"
								class="border border-gray-300 rounded px-3 w-full">
								<option value="">@lang('forum::messages.editor.select')</option>
								@foreach($categories as $category)
									@if(old('forum_category_id') == $category->id)
										<option value="{{ $category->id }}" selected>{{ $category->name }}</option>
									@elseif(!empty($current_category_id) && $current_category_id == $category->id)
										<option value="{{ $category->id }}" selected>{{ $category->name }}</option>
									@else
										<option value="{{ $category->id }}">{{ $category->name }}</option>
									@endif
								@endforeach
							</select>
						</div>
						<div class="w-full md:w-1/12 px-2 flex items-center justify-center -mt-8">
							<i class="forum-close cursor-pointer"></i>
						</div>
					</div>
					
					<!-- Editor -->
					<div id="editor" class="mb-4">
						@if($forum_editor == 'tinymce' || empty($forum_editor))
							
							<textarea id="body"
								class="richText border border-gray-300 rounded px-3 py-2 w-full"
								name="body"
								placeholder="">{{ old('body') }}</textarea>
						@elseif($forum_editor == 'simplemde')
							<textarea id="simplemde"
								name="body"
								class="border border-gray-300 rounded px-3 py-2 w-full"
								placeholder="">{{ old('body') }}</textarea>
						@elseif($forum_editor == 'trumbowyg')
							<textarea class="trumbowyg border border-gray-300 rounded px-3 py-2 w-full"
								name="body"
								placeholder="@lang('forum::messages.editor.tinymce_placeholder')">
								{{ old('body') }}
							</textarea>
						@endif
					</div>
					
					<input type="hidden" name="_token" id="csrf_token_field" value="{{ csrf_token() }}">
					
					<!-- Footer (Color y Botones) -->
					<div id="new_discussion_footer" class="flex items-center">
						<input type="text"
							id="color"
							name="color"
							class="border border-gray-300 rounded px-3 py-2 mr-2">
						<span class="select_color_text text-gray-700 mr-4">
							@lang('forum::messages.editor.select_color_text')
						</span>
						<button id="submit_discussion"
								class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded ml-auto">
							<i class="forum-new mr-1"></i> 
							@lang('forum::messages.discussion.create')
						</button>
						<a href="/{{ Config::get('forum.routes.home') }}"
							id="cancel_discussion"
							class="bg-gray-600 hover:bg-gray-600 text-white hover:text-white font-bold py-2 px-4 rounded ml-2">
							@lang('forum::messages.words.cancel')
						</a>
					</div>
				</form>
			</div>
		</div>

    </div>

    @if($forum_editor == 'tinymce' || empty($forum_editor))
        <input type="hidden" id="forum_tinymce_toolbar" value="{{ Config::get('forum.tinymce.toolbar') }}">
        <input type="hidden" id="forum_tinymce_plugins" value="{{ Config::get('forum.tinymce.plugins') }}">
    @endif
    <input type="hidden" id="current_path" value="{{ Request::path() }}">
@endsection

@section(Config::get('forum.yields.footer'))
    @if($forum_editor == 'tinymce' || empty($forum_editor))
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/tinymce.js') }}"></script>
        <script>
            var my_tinymce = tinyMCE;
            $('document').ready(function(){
                $('#tinymce_placeholder').click(function(){
                    my_tinymce.activeEditor.focus();
                });
            });
        </script>
    @elseif($forum_editor == 'simplemde')
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/simplemde.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/forum_simplemde.js') }}"></script>
    @elseif($forum_editor == 'trumbowyg')
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/trumbowyg.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/plugins/preformatted/trumbowyg.preformatted.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/trumbowyg.js') }}"></script>
    @endif

    <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/spectrum/spectrum.js') }}"></script>
    <script src="{{ url('/vendor/innoboxrr/forum/assets/js/forum.js') }}"></script>
    <script>
        $('document').ready(function(){
            $('.forum-close, #cancel_discussion').click(function(){
                $('#new_discussion').slideUp();
            });
            $('#new_discussion_btn').click(function(){
                @if(Auth::guest())
                    window.location.href = "{{ route('login') }}";
                @else
                    $('#new_discussion').slideDown();
                    $('#title').focus();
                @endif
            });

            $("#color").spectrum({
                color: "#333639",
                preferredFormat: "hex",
                containerClassName: 'forum-color-picker',
                cancelText: '',
                chooseText: 'close',
                move: function(color) {
                    $("#color").val(color.toHexString());
                }
            });

            @if(count($errors) > 0)
                $('#new_discussion').slideDown();
                $('#title').focus();
            @endif
        });
    </script>
@stop
