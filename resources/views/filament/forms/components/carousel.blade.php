<div x-data="{
    currentSlide: 0,
    totalSlides: 0,
    isDarkMode: false, // Set this to true if the dark mode is active

    init() {
        this.totalSlides = this.$refs.carousel.children.length;
    },
    goToSlide(index) {
        this.currentSlide = index;
        this.updateCarousel();
    },
    updateCarousel() {
        const offset = -this.currentSlide * 100;
        this.$refs.carousel.style.transform = `translateX(${offset}%)`;
    }
}" x-init="init()" class="max-w-xl mx-auto mt-8">
    <div class="relative overflow-hidden">
        <div x-ref="carousel" class="flex transition-transform ease-in-out duration-300 transform">

            @foreach ($getValue() as $index => $imageSource)
                <div class="w-full flex-shrink-0 " style="height: 140px;">
                    <img src="{{ $imageSource['preview_url'] }}" alt="Image 1" class="w-full h-full object-contain">
                </div>
            @endforeach
        </div>
    </div>
    <div class="flex justify-center mt-4">
        <template x-for="(slide, index) in Array.from({ length: totalSlides })" :key="index">
            <button x-on:click="goToSlide(index)"
                :class="{
                    'bg-primary-600': currentSlide === index,
                    'bg-gray-500/10 dark:bg-gray-500/20': currentSlide !==
                        index
                }"
                class="mx-1 w-3 h-3 rounded-full mr-1"></button>
        </template>
    </div>
</div>
