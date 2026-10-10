@extends('admin.layouts.ecommerce')

@section('title', $title)

@section('content')
<style>
    .editor-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}.editor-head h1{display:flex;gap:10px;align-items:center;margin:0;font-size:23px}.editor-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .editor-layout{display:grid;grid-template-columns:minmax(0,1fr) 310px;gap:18px;align-items:start}.editor-main,.editor-side{display:grid;gap:16px;min-width:0}.editor-panel{padding:20px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.editor-panel h2{margin:0 0 15px;font-size:15px}
    .editor-field{display:grid;gap:7px;margin-bottom:14px}.editor-field:last-child{margin-bottom:0}.editor-field label,.editor-label{color:var(--text);font-size:11px;font-weight:800;letter-spacing:.04em}.editor-field input,.editor-field textarea,.editor-field select{width:100%;min-height:42px;padding:10px 12px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}.editor-field textarea{min-height:125px;resize:vertical;line-height:1.65}.editor-field textarea.content-editor{min-height:380px;font-size:13px}.editor-field input:focus,.editor-field textarea:focus{outline:0;border-color:var(--brand);box-shadow:0 0 0 3px rgba(14,90,117,.1)}
    .slug-preview{color:var(--muted);font-size:10px;overflow-wrap:anywhere}.form-hint{color:var(--muted);font-size:10px;line-height:1.5}.editor-check{display:flex;align-items:flex-start;gap:9px;color:var(--text);font-size:12px}.editor-check input{margin-top:2px;accent-color:var(--brand)}
    .check-list{display:grid;gap:7px;max-height:210px;overflow:auto;padding:11px;border:1px solid var(--border);border-radius:10px;background:var(--bg)}.check-list label{display:flex;align-items:flex-start;gap:8px;color:var(--text);font-size:11px}.check-list input{margin-top:2px;accent-color:var(--brand)}
    .taxonomy-add{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:7px;margin-top:10px}.taxonomy-add input{min-width:0;min-height:38px;padding:8px 10px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:11px}.taxonomy-add .button{white-space:nowrap}.taxonomy-feedback{display:block;min-height:15px;margin-top:6px}.taxonomy-feedback.is-error{color:#b83232}
    .current-cover{width:100%;max-height:180px;object-fit:cover;border:1px solid var(--border);border-radius:11px}.publish-status{display:flex;align-items:center;gap:8px;padding:11px;border-radius:10px;background:var(--bg);color:var(--text);font-size:12px}
    .editor-actions{display:flex;gap:8px;flex-wrap:wrap}.editor-actions .button{flex:1;justify-content:center}.source-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);gap:8px;margin-bottom:8px}.source-row input{min-width:0;padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:11px}.source-remove{border:1px solid #f2d8d8;border-radius:8px;background:#fff5f5;color:#b83232;cursor:pointer}
    @media(max-width:900px){.editor-layout{grid-template-columns:minmax(0,1fr) 260px}}@media(max-width:720px){.editor-layout{grid-template-columns:1fr}.editor-side{grid-template-columns:1fr 1fr}.editor-side .editor-panel:first-child{grid-column:1/-1}}@media(max-width:520px){.editor-side{grid-template-columns:1fr}.editor-side .editor-panel:first-child{grid-column:auto}.editor-panel{padding:16px}.source-row{grid-template-columns:1fr}}
</style>
<header class="editor-head"><div><h1><i class="ri-edit-box-line"></i>{{ $title }}</h1><p>Write a useful original article, organize it, and control how search engines see it.</p></div><a class="button" href="{{ route('admin.blog.posts') }}"><i class="ri-arrow-left-line"></i>Back to posts</a></header>
@if($errors->any())<div class="errors" role="alert"><strong>Please correct the fields below.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ $post->exists ? route('admin.blog.posts.update',$post) : route('admin.blog.posts.store') }}" enctype="multipart/form-data" id="blogPostForm">
    @csrf
    @if($post->exists) @method('PATCH') @endif
    <input type="hidden" name="save_action" id="saveAction" value="save">
    <div class="editor-layout">
        <div class="editor-main">
            <section class="editor-panel">
                <h2>Article details</h2>
                <div class="editor-field"><label for="postTitle">Post title *</label><input id="postTitle" name="title" required maxlength="180" value="{{ old('title',$post->title) }}" placeholder="A clear, helpful title"></div>
                <div class="editor-field"><label for="postSlug">Permalink</label><input id="postSlug" name="slug" maxlength="200" value="{{ old('slug',$post->slug) }}" placeholder="Generated from your title"><span class="slug-preview">Public URL: {{ url('/blog') }}/<span id="slugPreview">{{ old('slug',$post->slug ?: 'your-post-title') }}</span></span></div>
                <div class="editor-field"><label for="postExcerpt">Short description</label><textarea id="postExcerpt" name="excerpt" rows="3" maxlength="320" placeholder="A short preview used on the journal cards and search results.">{{ old('excerpt',$post->excerpt) }}</textarea><span class="form-hint">Keep this concise and informative (up to 320 characters).</span></div>
                <div class="editor-field"><label for="postContent">Article content *</label><textarea class="content-editor" id="postContent" name="content" required maxlength="50000" placeholder="Write your original article here. Separate paragraphs with a blank line.">{{ old('content',$post->content) }}</textarea><span class="form-hint">Plain text is rendered safely with paragraph spacing. Avoid medical claims; link to trusted sources where appropriate.</span></div>
            </section>
            <section class="editor-panel">
                <h2>Search appearance</h2>
                <div class="editor-field"><label for="metaTitle">SEO title</label><input id="metaTitle" name="meta_title" maxlength="180" value="{{ old('meta_title',$post->meta_title) }}" placeholder="Uses the post title when blank"></div>
                <div class="editor-field"><label for="metaDescription">SEO description</label><textarea id="metaDescription" name="meta_description" rows="3" maxlength="320" placeholder="Describe the article in search results.">{{ old('meta_description',$post->meta_description) }}</textarea></div>
                <div class="editor-field"><label for="canonicalUrl">Canonical URL (optional)</label><input type="url" id="canonicalUrl" name="canonical_url" maxlength="2048" value="{{ old('canonical_url',$post->canonical_url) }}" placeholder="https://example.com/original-source"></div>
                <label class="editor-check"><input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable',$post->exists ? $post->is_indexable : true))><span>Allow search engines to index this post</span></label>
            </section>
            <section class="editor-panel">
                <h2>Research sources</h2>
                <p class="form-hint" style="margin:-7px 0 12px">Cite trustworthy references used to inform the article. These links are shown at the end of the published post.</p>
                <div id="sourceRows">
                    @foreach(old('source_links',$post->source_links ?? []) as $index=>$source)
                        <div class="source-row"><input name="source_links[{{ $index }}][label]" value="{{ $source['label'] ?? '' }}" maxlength="120" placeholder="Source title"><input type="url" name="source_links[{{ $index }}][url]" value="{{ $source['url'] ?? '' }}" maxlength="2048" placeholder="https://..."><button type="button" class="source-remove" aria-label="Remove source"><i class="ri-close-line"></i></button></div>
                    @endforeach
                </div>
                <button type="button" class="button" id="addSource"><i class="ri-add-line"></i>Add source</button>
            </section>
        </div>
        <aside class="editor-side">
            <section class="editor-panel">
                <h2>Publish</h2>
                <div class="editor-field"><label for="postStatus">Status *</label><select id="postStatus" name="status"><option value="draft" @selected(old('status',$post->status)==='draft')>Draft</option><option value="published" @selected(old('status',$post->status)==='published')>Published</option></select></div>
                @if($post->published_at)<div class="publish-status"><i class="ri-calendar-check-line"></i>{{ $post->published_at->format('M j, Y g:i A') }}</div>@endif
                <div class="editor-actions" style="margin-top:12px"><button type="submit" class="button primary" onclick="document.getElementById('saveAction').value='save'"><i class="ri-save-line"></i>Save</button><button type="submit" class="button" onclick="document.getElementById('saveAction').value='save-exit'">Save &amp; Exit</button></div>
            </section>
            <section class="editor-panel">
                <h2>Featured image</h2>
                <img class="current-cover" id="featuredImagePreview" src="{{ $post->featured_image ? asset('storage/'.$post->featured_image) : '' }}" alt="Featured post cover" @if(!$post->featured_image) hidden @endif>
                <input type="hidden" name="featured_image_path" id="featuredImagePath" value="{{ old('featured_image_path') }}">
                <button class="button" type="button" id="chooseFeaturedImage">Choose from Media library</button>
                <span class="form-hint">Upload images from Admin → Media, then select them here.</span>
                <label class="editor-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$post->is_featured))><span>Feature this story</span></label>
            </section>
            <section class="editor-panel">
                <h2>Categories</h2>
                <div class="check-list" id="blogCategoryChoices">
                    @forelse($categories as $category)<label><input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id,(array)old('categories',$post->exists?$post->categories->modelKeys():[])))><span>{{ $category->name }}</span></label>@empty<p class="form-hint" data-taxonomy-empty>No categories yet. Add one below.</p>@endforelse
                </div>
                <div class="taxonomy-add"><input id="quickBlogCategoryName" maxlength="100" placeholder="New category name" aria-label="New category name"><button class="button" type="button" data-create-taxonomy="category" data-url="{{ route('admin.blog.categories.store') }}"><i class="ri-add-line"></i>Add</button></div>
                <span class="form-hint taxonomy-feedback" id="categoryFeedback" role="status" aria-live="polite"></span>
            </section>
            <section class="editor-panel">
                <h2>Tags</h2>
                <div class="check-list" id="blogTagChoices">
                    @forelse($tags as $tag)<label><input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id,(array)old('tags',$post->exists?$post->tags->modelKeys():[])))><span>{{ $tag->name }}</span></label>@empty<p class="form-hint" data-taxonomy-empty>No tags yet. Add one below.</p>@endforelse
                </div>
                <div class="taxonomy-add"><input id="quickBlogTagName" maxlength="80" placeholder="New tag name" aria-label="New tag name"><button class="button" type="button" data-create-taxonomy="tag" data-url="{{ route('admin.blog.tags.store') }}"><i class="ri-add-line"></i>Add</button></div>
                <span class="form-hint taxonomy-feedback" id="tagFeedback" role="status" aria-live="polite"></span>
            </section>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const title=document.getElementById('postTitle'),slug=document.getElementById('postSlug'),preview=document.getElementById('slugPreview');
    let manuallyEdited=Boolean(slug.value);
    slug.addEventListener('input',()=>{manuallyEdited=true;preview.textContent=slug.value||'your-post-title'});
    title.addEventListener('input',()=>{if(manuallyEdited)return;slug.value=title.value.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');preview.textContent=slug.value||'your-post-title'});
    const addSource=()=>{const index=document.querySelectorAll('#sourceRows .source-row').length;const row=document.createElement('div');row.className='source-row';row.innerHTML='<input maxlength="120" placeholder="Source title"><input type="url" maxlength="2048" placeholder="https://..."><button type="button" class="source-remove" aria-label="Remove source"><i class="ri-close-line"></i></button>';row.querySelectorAll('input').forEach((input,i)=>input.name='source_links['+index+']['+(i?'url':'label')+']');document.getElementById('sourceRows').appendChild(row);};
    const featuredImagePath=document.getElementById('featuredImagePath');
    document.getElementById('chooseFeaturedImage').addEventListener('click',()=>{
        if(typeof window.openMediaPicker!=='function'){window.alert('The Media library is unavailable. Refresh the page and try again.');return;}
        window.openMediaPicker(image=>{
            featuredImagePath.value=image.path;
            const preview=document.getElementById('featuredImagePreview');
            preview.src=image.url;
            preview.alt=image.name;
            preview.hidden=false;
        });
    });
    document.getElementById('addSource').addEventListener('click',addSource);
    document.getElementById('sourceRows').addEventListener('click',event=>{const button=event.target.closest('.source-remove');if(button)button.closest('.source-row').remove()});
    document.querySelectorAll('[data-create-taxonomy]').forEach(button=>{
        button.addEventListener('click',async()=>{
            const type=button.dataset.createTaxonomy;
            const isCategory=type==='category';
            const input=document.getElementById(isCategory?'quickBlogCategoryName':'quickBlogTagName');
            const list=document.getElementById(isCategory?'blogCategoryChoices':'blogTagChoices');
            const feedback=document.getElementById(isCategory?'categoryFeedback':'tagFeedback');
            const name=input.value.trim();
            feedback.textContent='';
            feedback.classList.remove('is-error');
            if(!name){feedback.textContent='Enter a name first.';feedback.classList.add('is-error');input.focus();return;}
            button.disabled=true;
            try{
                const response=await fetch(button.dataset.url,{
                    method:'POST',
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                    body:new URLSearchParams({name})
                });
                const data=await response.json().catch(()=>({}));
                if(!response.ok){
                    const errors=data.errors||{};
                    const firstError=Object.values(errors).flat()[0];
                    throw new Error(firstError||data.message||'Could not create this '+type+'.');
                }
                const item=data[type];
                if(!item||!item.id||!item.name)throw new Error('The server returned an invalid '+type+' response.');
                list.querySelector('[data-taxonomy-empty]')?.remove();
                const label=document.createElement('label');
                const checkbox=document.createElement('input');
                checkbox.type='checkbox';
                checkbox.name=isCategory?'categories[]':'tags[]';
                checkbox.value=String(item.id);
                checkbox.checked=true;
                const text=document.createElement('span');
                text.textContent=item.name;
                label.append(checkbox,text);
                list.append(label);
                input.value='';
                feedback.textContent=item.name+' created and selected.';
            }catch(error){
                feedback.textContent=error.message||'Could not create this '+type+'.';
                feedback.classList.add('is-error');
            }finally{
                button.disabled=false;
            }
        });
    });
})();
</script>
@endpush
