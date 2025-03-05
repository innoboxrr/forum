@extends(Config::get('forum.master_file_extend'))

@section(Config::get('forum.yields.head'))
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @if(Config::get('forum.sidebar_in_discussion_view'))
        <link href="{{ url('/vendor/innoboxrr/forum/assets/vendor/spectrum/spectrum.css') }}" rel="stylesheet">
    @endif
    <link href="{{ url('/vendor/innoboxrr/forum/assets/css/forum.css') }}" rel="stylesheet">
    @if($forum_editor == 'simplemde')
        <link href="{{ url('/vendor/innoboxrr/forum/assets/css/simplemde.min.css') }}" rel="stylesheet">
    @elseif($forum_editor == 'trumbowyg')
        <link href="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/ui/trumbowyg.css') }}" rel="stylesheet">
        <style>
            .trumbowyg-box, .trumbowyg-editor {
                margin: 0 auto;
            }
        </style>
    @endif
@stop

@section('content')
    <div id="forum" class="discussion">
        {{-- Encabezado del foro --}}
        <div id="forum_header" style="background-color: {{ $discussion->color }};">
			<div class="container mx-auto px-4 flex flex-wrap items-center justify-between max-w-4xl lg:max-w-6xl">
				<!-- Título e ícono de regreso -->
				<div class="flex items-center">
					<a href="/{{ Config::get('forum.routes.home') }}" class="text-blue-600 hover:underline">
						<i class="forum-back"></i>
					</a>
					<h1 class="text-2xl font-semibold ml-4">{{ $discussion->title }}</h1>
				</div>
		
				<!-- Detalles y Categoría -->
				<div class="forum_head_details text-sm text-gray-700 flex items-center">
					<span>@lang('forum::messages.discussion.head_details')</span>
					<a href="/{{ Config::get('forum.routes.home') }}/{{ Config::get('forum.routes.category') }}/{{ $discussion->category->slug }}" 
					   class="forum_cat inline-block px-3 py-1 ml-3 rounded text-white text-sm font-medium"
					   style="background-color: {{ $discussion->category->color }};">
						{{ $discussion->category->name }}
					</a>
				</div>
			</div>
		</div>

        {{-- Mensajes de error y alertas --}}
        @if(config('forum.errors'))
            @if(Session::has('forum_alert'))
                <div class="forum-alert bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded">
                    <div class="container mx-auto">
                        <strong>
                            <i class="forum-alert-{{ Session::get('forum_alert_type') }}"></i>
                            {{ Config::get('forum.alert_messages.' . Session::get('forum_alert_type')) }}
                        </strong>
                        {{ Session::get('forum_alert') }}
                        <i class="forum-close cursor-pointer"></i>
                    </div>
                </div>
                <div class="my-2"></div>
            @endif

            @if(count($errors) > 0)
                <div class="forum-alert bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
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

        {{-- Contenido principal --}}
        <div class="container mx-auto px-4 mt-6 max-w-4xl lg:max-w-6xl">
            <div class="flex flex-wrap -mx-4">
                @if(! Config::get('forum.sidebar_in_discussion_view'))
                    <div class="w-full">
                @else
                    <div class="w-full md:w-1/4 px-4 mb-4 md:mb-0">
                        {{-- Sidebar --}}
                        <div class="forum_sidebar bg-white rounded p-4">
                            <button id="new_discussion_btn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                                <i class="forum-new"></i> @lang('forum::messages.discussion.new')
                            </button>
                            <a href="/{{ Config::get('forum.routes.home') }}" class="flex items-center text-blue-500 hover:underline mt-4">
                                <i class="forum-bubble mr-2"></i> @lang('forum::messages.discussion.all')
                            </a>
                            <ul class="mt-4 space-y-2">
                                <?php $categories = Innoboxrr\Forum\Models\Models::category()->all(); ?>
                                @foreach($categories as $category)
                                    <li>
                                        <a href="/{{ Config::get('forum.routes.home') }}/{{ Config::get('forum.routes.category') }}/{{ $category->slug }}" class="flex items-center text-gray-800 hover:text-blue-500">
                                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $category->color }};"></div>
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="w-full md:w-3/4 px-4">
                @endif

                    {{-- Conversación --}}
                    <div class="conversation">
                        <ul class="discussions" style="display: block;">
                            @foreach($posts as $post)
                                <li data-id="{{ $post->id }}" data-markdown="{{ $post->markdown }}" class="py-4 border-b border-gray-200">
                                    <span class="forum_posts block">
                                        @if(!Auth::guest() && (Auth::user()->id == $post->user->id))
                                            <div id="delete_warning_{{ $post->id }}" class="forum_warning_delete bg-yellow-100 border border-yellow-300 text-yellow-800 px-3 py-2 rounded mb-2 hidden">
                                                <i class="forum-warning"></i> @lang('forum::messages.response.confirm')
                                                <button class="delete_response bg-red-500 hover:bg-red-600 text-white text-sm py-1 px-2 rounded float-right">
                                                    @lang('forum::messages.response.yes_confirm')
                                                </button>
                                                <button class="btn-default bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm py-1 px-2 rounded float-right mr-2">
                                                    @lang('forum::messages.response.no_confirm')
                                                </button>
                                            </div>
                                            <div class="forum_post_actions flex space-x-4 mb-2">
                                                <p class="forum_delete_btn text-red-500 cursor-pointer">
                                                    <i class="forum-delete"></i> @lang('forum::messages.words.delete')
                                                </p>
                                                <p class="forum_edit_btn text-blue-500 cursor-pointer">
                                                    <i class="forum-edit"></i> @lang('forum::messages.words.edit')
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Avatar del usuario --}}
                                        <div class="forum_avatar inline-block align-top mr-4">
                                            @if(Config::get('forum.user.avatar_image_database_field'))
                                                <?php $db_field = Config::get('forum.user.avatar_image_database_field'); ?>
                                                @if((substr($post->user->{$db_field}, 0, 7) == 'http://') || (substr($post->user->{$db_field}, 0, 8) == 'https://'))
                                                    <img src="{{ $post->user->{$db_field} }}" class="w-10 h-10 rounded-full">
                                                @else
                                                    <img src="{{ Config::get('forum.user.relative_url_to_image_assets') . $post->user->{$db_field} }}" class="w-10 h-10 rounded-full">
                                                @endif
                                            @else
                                                <span class="forum_avatar_circle inline-flex items-center justify-center w-10 h-10 rounded-full" style="background-color: #<?= \Innoboxrr\Forum\Helpers\ForumHelper::stringToColorCode($post->user->{Config::get('forum.user.database_field_with_user_name')}) ?>">
                                                    {{ ucfirst(substr($post->user->{Config::get('forum.user.database_field_with_user_name')}, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Contenido del post --}}
                                        <div class="forum_middle inline-block w-[calc(100%-3rem)] align-top">
                                            <span class="forum_middle_details text-sm text-gray-600">
                                                <a href="{{ \Innoboxrr\Forum\Helpers\ForumHelper::userLink($post->user) }}" class="font-medium hover:underline">
                                                    {{ ucfirst($post->user->{Config::get('forum.user.database_field_with_user_name')}) }}
                                                </a>
                                                <span class="ago ml-1">
                                                    {{ \Carbon\Carbon::createFromTimeStamp(strtotime($post->created_at))->diffForHumans() }}
                                                </span>
                                            </span>
                                            <div class="forum_body text-gray-800 mt-2">
                                                @if($post->markdown)
                                                    <pre class="forum_body_md whitespace-pre-wrap font-mono text-sm">{{ $post->body }}</pre>
                                                    {!! \Innoboxrr\Forum\Helpers\ForumHelper::demoteHtmlHeaderTags(GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($post->body)) !!}
                                                @else
                                                    {!! $post->body !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="clear-both"></div>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Paginación --}}
                    <div id="pagination" class="mt-4">
                        {{ $posts->links() }}
                    </div>

                    @if(!Auth::guest())
						<div id="new_response" class="container mx-auto px-4 mt-6 max-w-4xl lg:max-w-6xl">
							<div class="flex items-start gap-4">
								<!-- Avatar del usuario autenticado -->
								<div class="inline-block align-top mr-4">
									@if(Config::get('forum.user.avatar_image_database_field'))
										<?php $db_field = Config::get('forum.user.avatar_image_database_field'); ?>
										@if(str_starts_with(Auth::user()->{$db_field}, 'http://') || str_starts_with(Auth::user()->{$db_field}, 'https://'))
											<img src="{{ Auth::user()->{$db_field} }}" class="w-10 h-10 rounded-full">
										@else
											<img src="{{ Config::get('forum.user.relative_url_to_image_assets') . Auth::user()->{$db_field} }}" class="w-10 h-10 rounded-full">
										@endif
									@else
										<span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-white font-semibold" 
											style="background-color: #{{ \Innoboxrr\Forum\Helpers\ForumHelper::stringToColorCode(Auth::user()->{Config::get('forum.user.database_field_with_user_name')}) }}">
											{{ strtoupper(substr(Auth::user()->{Config::get('forum.user.database_field_with_user_name')}, 0, 1)) }}
										</span>
									@endif
								</div>
						
								<!-- Formulario para nueva respuesta -->
								<div class="flex-1">
									{{-- Formulario para nueva respuesta --}}
									<div id="new_discussion" class="mt-4">
										<div class="forum_loader dark hidden" id="new_discussion_loader">
											<div class="w-6 h-6 border-4 border-t-blue-500 border-gray-200 rounded-full animate-spin"></div>
										</div>

										<form id="forum_form_editor" action="/{{ Config::get('forum.routes.home') }}/posts" method="POST">
											<div id="editor" class="mb-4">
												@if($forum_editor == 'tinymce' || empty($forum_editor))
													<label id="tinymce_placeholder" class="block text-gray-500 mb-2">
														@lang('forum::messages.editor.tinymce_placeholder')
													</label>
													<textarea id="body" class="richText border border-gray-300 rounded px-3 py-2 w-full" name="body" placeholder="">{{ old('body') }}</textarea>
												@elseif($forum_editor == 'simplemde')
													<textarea id="simplemde" name="body" placeholder="" class="border border-gray-300 rounded px-3 py-2 w-full">{{ old('body') }}</textarea>
												@elseif($forum_editor == 'trumbowyg')
													<textarea class="trumbowyg border border-gray-300 rounded px-3 py-2 w-full" name="body" placeholder="Type Your Discussion Here...">{{ old('body') }}</textarea>
												@endif
											</div>

											<input type="hidden" name="_token" id="csrf_token_field" value="{{ csrf_token() }}">
											<input type="hidden" name="forum_discussion_id" value="{{ $discussion->id }}">
										</form>
									</div><!-- #new_discussion -->

									{{-- Sección para notificación vía email --}}
									<div id="discussion_response_email" class="mt-4 flex items-center">
										<button id="submit_response" class="ml-auto bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded flex items-center">
											<i class="forum-new mr-1"></i> @lang('forum::messages.response.submit')
										</button>
										@if(Config::get('forum.email.enabled'))
											<div id="notify_email" class="flex items-center ml-4">
												<img src="{{ url('/vendor/innoboxrr/forum/assets/images/email.gif') }}" class="w-6 h-6 mr-2 forum_email_loader">
												<span class="text-gray-700 mr-2">@lang('forum::messages.email.notify')</span>
												<label class="switch relative inline-block w-10 h-5">
													<input type="checkbox" id="email_notification" name="email_notification" class="opacity-0 w-0 h-0" @if(!Auth::guest() && $discussion->users->contains(Auth::user()->id)) checked @endif>
													<span class="on absolute left-0 top-0 w-5 h-5 bg-green-500 rounded-full"></span>
													<span class="off absolute right-0 top-0 w-5 h-5 bg-red-500 rounded-full"></span>
													<div class="slider absolute inset-0 rounded-full bg-gray-300"></div>
												</label>
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>
                    @else
                        <div id="login_or_register" class="mt-6 p-4 bg-gray-100 rounded text-center text-gray-700">
                            <p>@lang('forum::messages.auth', ['home' => Config::get('forum.routes.home')])</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(Config::get('forum.sidebar_in_discussion_view'))
            <div id="new_discussion_in_discussion_view" class="container mx-auto px-4 mt-6 max-w-4xl lg:max-w-6xl">
                <div class="forum_loader dark hidden" id="new_discussion_loader_in_discussion_view">
                    <div class="w-6 h-6 border-4 border-t-blue-500 border-gray-200 rounded-full animate-spin"></div>
                </div>

                <form id="forum_form_editor_in_discussion_view" action="/{{ Config::get('forum.routes.home') }}/{{ Config::get('forum.routes.discussion') }}" method="POST" class="bg-white rounded p-4">
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-7/12 px-2">
                            <input type="text" id="title" name="title" placeholder="@lang('forum::messages.editor.title')" v-model="title" value="{{ old('title') }}" class="border border-gray-300 rounded px-3 py-2 w-full">
                        </div>
                        <div class="w-full md:w-4/12 px-2">
                            <select id="forum_category_id" name="forum_category_id" class="border border-gray-300 rounded px-3 py-2 w-full">
                                <option value="">@lang('forum::messages.editor.select')</option>
                                @foreach($categories as $category)
                                    @if(old('forum_category_id') == $category->id)
                                        <option value="{{ $category->id }}" selected>{{ $category->name }}</option>
                                    @else
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/12 px-2 flex items-center justify-center">
                            <i class="forum-close cursor-pointer"></i>
                        </div>
                    </div>
                    <div id="editor" class="mb-4">
                        @if($forum_editor == 'tinymce' || empty($forum_editor))
                            <label id="tinymce_placeholder" class="block text-gray-500 mb-2">Add the content for your Discussion here</label>
                            <textarea id="body_in_discussion_view" class="richText border border-gray-300 rounded px-3 py-2 w-full" name="body" placeholder="">{{ old('body') }}</textarea>
                        @elseif($forum_editor == 'simplemde')
                            <textarea id="simplemde_in_discussion_view" name="body" placeholder="" class="border border-gray-300 rounded px-3 py-2 w-full">{{ old('body') }}</textarea>
                        @elseif($forum_editor == 'trumbowyg')
                            <textarea class="trumbowyg border border-gray-300 rounded px-3 py-2 w-full" name="body" placeholder="">{{ old('body') }}</textarea>
                        @endif
                    </div>
                    <input type="hidden" name="_token" id="csrf_token_field" value="{{ csrf_token() }}">
                    <div id="new_discussion_footer" class="flex items-center">
                        <input type="text" id="color" name="color" class="border border-gray-300 rounded px-3 py-2 mr-2">
                        <span class="select_color_text text-gray-700 mr-4">@lang('forum::messages.editor.tinymce_placeholder')</span>
                        <button id="submit_discussion" class="ml-auto bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded flex items-center">
                            <i class="forum-new mr-1"></i> Create {{ Config::get('forum.titles.discussion') }}
                        </button>
                        <a href="/{{ Config::get('forum.routes.home') }}" id="cancel_discussion" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                            Cancel
                        </a>
                        <div class="clear-both"></div>
                    </div>
                </form>
            </div><!-- #new_discussion_in_discussion_view -->
        @endif
    </div>

    @if($forum_editor == 'tinymce' || empty($forum_editor))
        <input type="hidden" id="forum_tinymce_toolbar" value="{{ Config::get('forum.tinymce.toolbar') }}">
        <input type="hidden" id="forum_tinymce_plugins" value="{{ Config::get('forum.tinymce.plugins') }}">
    @endif
    <input type="hidden" id="current_path" value="{{ Request::path() }}">
@stop

@section(Config::get('forum.yields.footer'))
    @if($forum_editor == 'tinymce' || empty($forum_editor))
        <script>var forum_editor = 'tinymce';</script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/tinymce.js') }}"></script>
        <script>
            var my_tinymce = tinyMCE;
            $(document).ready(function(){
                $('#tinymce_placeholder').click(function(){
                    my_tinymce.activeEditor.focus();
                });
            });
        </script>
    @elseif($forum_editor == 'simplemde')
        <script>var forum_editor = 'simplemde';</script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/simplemde.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/forum_simplemde.js') }}"></script>
    @elseif($forum_editor == 'trumbowyg')
        <script>var forum_editor = 'trumbowyg';</script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/trumbowyg.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/vendor/trumbowyg/plugins/preformatted/trumbowyg.preformatted.min.js') }}"></script>
        <script src="{{ url('/vendor/innoboxrr/forum/assets/js/trumbowyg.js') }}"></script>
    @endif

    @if(Config::get('forum.sidebar_in_discussion_view'))
        <script src="/vendor/innoboxrr/forum/assets/vendor/spectrum/spectrum.js"></script>
    @endif

    <script src="{{ url('/vendor/innoboxrr/forum/assets/js/forum.js') }}"></script>

    <script>
        $(document).ready(function(){
            var simplemdeEditors = [];

            $('.forum_edit_btn').click(function(){
                var parent = $(this).parents('li');
                parent.addClass('editing');
                var id = parent.data('id');
                var markdown = parent.data('markdown');
                var container = parent.find('.forum_middle');

                var body;
                if(markdown){
                    body = container.find('.forum_body_md');
                } else {
                    body = container.find('.forum_body');
                    markdown = 0;
                }

                // Agregar dinámicamente un textarea para editar
                container.prepend('<textarea id="post-edit-' + id + '"></textarea>');
                $("#post-edit-" + id).text(body.html());
                container.append('<div class="forum_update_actions mt-2 flex space-x-2"><button class="update_forum_edit bg-green-500 hover:bg-green-600 text-white text-sm py-1 px-2 rounded" data-id="' + id + '" data-markdown="' + markdown + '"><i class="forum-check"></i> @lang('forum::messages.response.update')</button><button class="cancel_forum_edit bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm py-1 px-2 rounded" data-id="' + id + '" data-markdown="' + markdown + '">@lang('forum::messages.words.cancel')</button></div>');

                if(markdown){
                    simplemdeEditors['post-edit-' + id] = newSimpleMde(document.getElementById('post-edit-' + id));
                } else {
                    @if($forum_editor == 'tinymce' || empty($forum_editor))
                        initializeNewTinyMCE('post-edit-' + id);
                    @elseif($forum_editor == 'trumbowyg')
                        initializeNewTrumbowyg('post-edit-' + id);
                    @endif
                }
            });

            $('.discussions li').on('click', '.cancel_forum_edit', function(e){
                var post_id = $(e.target).data('id');
                var markdown = $(e.target).data('markdown');
                var parent_li = $(e.target).parents('li');
                var parent_actions = $(e.target).parent('.forum_update_actions');
                if(!markdown){
                    @if($forum_editor == 'tinymce' || empty($forum_editor))
                        tinymce.remove('#post-edit-' + post_id);
                    @elseif($forum_editor == 'trumbowyg')
                        $(e.target).parents('li').find('.trumbowyg').fadeOut();
                    @endif
                } else {
                    $(e.target).parents('li').find('.editor-toolbar').remove();
                    $(e.target).parents('li').find('.editor-preview-side').remove();
                    $(e.target).parents('li').find('.CodeMirror').remove();
                }
                $('#post-edit-' + post_id).remove();
                parent_actions.remove();
                parent_li.removeClass('editing');
            });

            $('.discussions li').on('click', '.update_forum_edit', function(e){
                var post_id = $(e.target).data('id');
                var markdown = $(e.target).data('markdown');
                var update_body;
                if(markdown){
                    update_body = simplemdeEditors['post-edit-' + post_id].value();
                } else {
                    @if($forum_editor == 'tinymce' || empty($forum_editor))
                        update_body = tinyMCE.get('post-edit-' + post_id).getContent();
                    @elseif($forum_editor == 'trumbowyg')
                        update_body = $('#post-edit-' + id).trumbowyg('html');
                    @endif
                }
                $.form('/{{ Config::get('forum.routes.home') }}/posts/' + post_id, { _token: '{{ csrf_token() }}', _method: 'PATCH', 'body' : update_body }, 'POST').submit();
            });

            $('#submit_response').click(function(){
                $('#forum_form_editor').submit();
            });

            $('.forum_delete_btn').click(function(){
                var parent = $(this).parents('li');
                parent.addClass('delete_warning');
                var id = parent.data('id');
                $('#delete_warning_' + id).show();
            });

            $('.forum_warning_delete .btn-default').click(function(){
                $(this).parent('.forum_warning_delete').hide();
                $(this).parents('li').removeClass('delete_warning');
            });

            $('.delete_response').click(function(){
                var post_id = $(this).parents('li').data('id');
                $.form('/{{ Config::get('forum.routes.home') }}/posts/' + post_id, { _token: '{{ csrf_token() }}', _method: 'DELETE'}, 'POST').submit();
            });

            @if(Config::get('forum.sidebar_in_discussion_view'))
                $('.forum-close, #cancel_discussion').click(function(){
                    $('#new_discussion_in_discussion_view').slideUp();
                });
                $('#new_discussion_btn').click(function(){
                    @if(Auth::guest())
                        window.location.href = "/{{ Config::get('forum.routes.home') }}/login";
                    @else
                        $('#new_discussion_in_discussion_view').slideDown();
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
                    $('#new_discussion_in_discussion_view').slideDown();
                    $('#title').focus();
                @endif
            @endif
        });
    </script>

    <script src="{{ url('/vendor/innoboxrr/forum/assets/js/forum.js') }}"></script>
@stop
