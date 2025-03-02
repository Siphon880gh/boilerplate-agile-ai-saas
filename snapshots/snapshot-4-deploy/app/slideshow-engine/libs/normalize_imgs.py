
from PIL import Image, ImageFilter, __version__
import os

# Check the Pillow version: Pillow v10 removed <Image>.getsize, so we need to add it back with the new <Image>.Resampling.LANCZOS 
if tuple(map(int, __version__.split('.'))) >= (10, 0, 0):
    Image.ANTIALIAS = Image.Resampling.LANCZOS

def letterbox_image(image, target_size, use_blurred_background_on_letterbox):
    print(f"Original image size: {image.size}")
    print(f"Target size: {target_size}")

    image_width, image_height = image.size
    aspect_ratio = image_width / image_height

    target_width, target_height = target_size
    new_width = int(target_height * aspect_ratio)
    new_height = target_height

    if new_width > target_width:
        new_width = target_width
        new_height = int(target_width / aspect_ratio)

    x_padding = (target_width - new_width) // 2
    y_padding = (target_height - new_height) // 2

    print(f"Resized image dimensions: {new_width}x{new_height}")
    print(f"Padding: x={x_padding}, y={y_padding}")

    # background = Image.new("RGBA", target_size, (0, 0, 0, 255))
    # image = image.resize((new_width, new_height), Image.ANTIALIAS)
    # background.paste(image, (x_padding, y_padding))

    # return background

    if use_blurred_background_on_letterbox:
        # Create a blurred background
        blur_scale = 0.2  # Adjust this for more or less blurriness
        blurred_background = image.resize((int(image_width * blur_scale), int(image_height * blur_scale)), Image.ANTIALIAS)
        blurred_background = blurred_background.filter(ImageFilter.GaussianBlur(radius=5))  # Adjust the radius for more or less blur
        blurred_background = blurred_background.resize(target_size, Image.ANTIALIAS)

        # Convert blurred background to match image mode if necessary
        if image.mode == 'RGBA' and blurred_background.mode != 'RGBA':
            blurred_background = blurred_background.convert('RGBA')

        # Resize original image
        image = image.resize((new_width, new_height), Image.ANTIALIAS)

        # If image has alpha channel, use it as the mask
        mask = image.split()[3] if image.mode == 'RGBA' else None
        blurred_background.paste(image, (x_padding, y_padding), mask)

        return blurred_background
    else:
        # Use black bars for letterboxing
        background = Image.new("RGBA", target_size, (0, 0, 0, 255))
        image = image.resize((new_width, new_height), Image.ANTIALIAS)
        background.paste(image, (x_padding, y_padding))

        return background


def letterbox_and_save_images(image_paths, min_width=None, min_height=None, use_blurred_background_on_letterbox=False):
    # Paths to the images
    cwd = os.getcwd()
    
    # Load the images
    images = [Image.open(os.path.join(cwd, path)) for path in image_paths]


    # Images are normalized to the largest width with the largest height of the images, NOT to the one image with the largest area (width x height)
    # Peek forward: Then normalize all images to this composed dimensions adding sidebars as necessary (and whether to use blurred background or black bars is another parameter).
    max_dimensions = max((img.size for img in images), key=lambda d: d[0]*d[1])
    if(min_width is not None):
        if(max_dimensions[0] < min_width):
            max_dimensions = (min_width, max_dimensions[1])
    if(min_height is not None):
        if(max_dimensions[1] < min_height):
            max_dimensions = (max_dimensions[0], min_height)

    print("")
    print(f"Calculated letterbox max dimensions: {max_dimensions}")
    
    # Letterbox and save the images
    for i, image in enumerate(images):
        if image.size != max_dimensions:
            letterboxed = letterbox_image(image, max_dimensions, use_blurred_background_on_letterbox)
            # save_path = os.path.splitext(image_paths[i])[0] + "_letterboxed.png" # Obsoleted. Decided to just override the original image to keep code complexity down
            save_path = image_paths[i]
            # Workaround: "OSError: cannot write mode RGBA as JPEG" (in this case, can't write alpha transparent channel into jpg)
            if letterboxed.mode != 'RGB':
                letterboxed = letterboxed.convert('RGB')    

            # TODO: Optimize the code by saving it to memory a list of images and returning it to engine_sequence for processing instead of re-saving to hard disk
            # The function doesn't return any values, and that list of images in memory should be returned in the future
            letterboxed.save(save_path)
            print(f"Saved letterboxed image to: {save_path}")

    return max_dimensions
