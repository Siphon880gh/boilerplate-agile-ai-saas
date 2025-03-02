from api_service import create_app

api_service = create_app()

if __name__ == "__main__":
    api_service.run(debug=True)