POST /api/register 

{
  "name": "Hassan Asqlany",
  "phone": "+201003232423",
  "email": "Haasqlany@example.com",
  "password": "secret123",
  "password_confirmantion": "secret123",
  "user_priviliages": "Trainee"
}

POST /api/organizations  //للردع ليس الشهادة


{
  "name": "Tech Innovators Ltd",
  "sector": "Information Technology",
  "established_at": "2015-04-22",
  "email": "contact@techinnovators.com",
  "phone": "+201001234567",
  "address": "123 Nile Street, Cairo, Egypt"
}

post   /api/organizations/{org_id}/axes/{axis}

* They all are booleans *


| Key            | Value             | Type |
| -------------- | ----------------- | ---- |
| `q1`           | `1`            | Text |
| `q2`           | `1`           | Text |
| `q3`           | `1`            | Text |
| `q4`           | `0`           | Text |
| `attachment_1` | *(choose a file)* | File |
| `attachment_2` | *(optional)*      | File |
| `attachment_3` | *(optional)*      | File |



1️⃣ Organization Registration
Endpoint

POST /api/certificates
Request Body
json{
  "organization_name": "جمعية الأمل للتنمية",
  "executive_name": "أحمد محمد",
  "email": "info@alamal.org",
  "phone": "+966501234567",
  "license_number": "CH-2024-001",
  "path": "strategic"
}
Response
{
  "message": "تم تسجيل الجهة بنجاح ✅",
  "data": {
    "certificate_id": 1
  }
}


2️⃣ Get Questions by Path
Endpoint
GET /api/certificates/questions/{path}

Example: Strategic Path

GET /api/certificates/questions/strategic

Response

{
  "data": [
    {
      "id": 1,
      "name": "المسار الاستراتيجي",
      "description": "محور خاص بالمسار الاستراتيجي",
      "path": "strategic",
      "weight": 100,
      "questions": [
        {
          "id": 1,
          "criteria_axis_id": 1,
          "question_text": "ما هو موعد نشر التقرير السنوي للجمعية لهذا العام؟",
          "options": [
            "قبل شهر 3",
            "بعد شهر 3",
            "بعد شهر 5",
            "بعد شهر 6",
            "بعد شهر 7",
            "بعد شهر 8",
            "بعد شهر 9",
            "بعد شهر 10"
          ],
          "points_mapping": {
            "قبل شهر 3": 15,
            "بعد شهر 3": 10,
            "بعد شهر 5": 8,
            "بعد شهر 6": 6,
            "بعد شهر 7": 5,
            "بعد شهر 8": 4,
            "بعد شهر 9": 3,
            "بعد شهر 10": 2
          },
          "attachment_required": true,
          "weight": 1.0
        }
      ]
    }
  ]
}


3️⃣ Submit Answers
Endpoint
POST /api/certificates/{certificateId}/answers
Request (Multipart Form Data)
Strategic Path Example
POST /api/certificates/1/answers
Content-Type: multipart/form-data

answers[0][question_id]: 1
answers[0][selected_option]: "قبل شهر 3"
answers[0][attachment]: [FILE]

answers[1][question_id]: 2
answers[1][selected_option]: "من 86 - 100"
answers[1][attachment]: [FILE]

answers[2][question_id]: 3
answers[2][selected_option]: "من 76 - 85%"
answers[2][attachment]: [FILE]

answers[3][question_id]: 4
answers[3][selected_option]: "تم النشر"
answers[3][attachment]: [FILE]

answers[4][question_id]: 5
answers[4][selected_option]: "تم النشر"
answers[4][attachment]: [FILE]
Response
json{
  "message": "تم إرسال الإجابات والملفات بنجاح ✅",
  "data": {
    "final_score": 120.0,
    "final_rank": "diamond"
  }
}
