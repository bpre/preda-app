<div class="relative mt-8 flex items-center gap-x-4">
    <img
        src="/images/team/min/{{  image_from_email($author->email) }}"
        alt="{{  $author->name }}"
        class="size-10 rounded-full bg-secondary-50"
    />
    <div class="text-sm/6">
        <p class="font-semibold text-primary-900">
            <a href="#">
            <span class="absolute inset-0"></span>
            {{  $author->name }}
            </a>
        </p>
        <p class="text-secondary-600">{{  $author->website_title }}</p>
    </div>
</div>
