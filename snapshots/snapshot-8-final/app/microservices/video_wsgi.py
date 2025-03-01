from video_engine import create_app

video_engine = create_app()

if __name__ == "__main__":
    video_engine.run(debug=True)