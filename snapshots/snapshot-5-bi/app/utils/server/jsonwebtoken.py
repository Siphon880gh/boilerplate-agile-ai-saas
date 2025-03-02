import base64
import hmac
import hashlib
import json
import time

def base64_url_encode(data):
    """Encodes data to Base64 URL-safe format."""
    return base64.urlsafe_b64encode(data).rstrip(b"=").decode('utf-8')

def base64_url_decode(data):
    """Decodes Base64 URL-safe data."""
    padding = '=' * (-len(data) % 4)
    return base64.urlsafe_b64decode(data + padding)

def generate_jwt(payload, secret):
    """Generates a JSON Web Token."""
    header = {"alg": "HS256", "typ": "JWT"}
    header_encoded = base64_url_encode(json.dumps(header).encode('utf-8'))
    payload_encoded = base64_url_encode(json.dumps(payload).encode('utf-8'))
    signature = hmac.new(
        secret.encode('utf-8'),
        f"{header_encoded}.{payload_encoded}".encode('utf-8'),
        hashlib.sha256
    ).digest()
    signature_encoded = base64_url_encode(signature)
    return f"{header_encoded}.{payload_encoded}.{signature_encoded}"

def decode_jwt(token, secret):
    """Decodes and verifies a JSON Web Token."""
    try:
        header_encoded, payload_encoded, signature_encoded = token.split(".")
        signature = hmac.new(
            secret.encode('utf-8'),
            f"{header_encoded}.{payload_encoded}".encode('utf-8'),
            hashlib.sha256
        ).digest()
        valid_signature = base64_url_encode(signature)
        if not hmac.compare_digest(valid_signature, signature_encoded):
            return {"error": "Invalid token"}
        payload = json.loads(base64_url_decode(payload_encoded).decode('utf-8'))
        if "exp" in payload and time.time() > payload["exp"]:
            return {"error": "Token has expired"}
        return payload
    except Exception as e:
        return {"error": f"Invalid token structure: {e}"}