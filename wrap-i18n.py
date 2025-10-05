#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
WordPress i18n Wrapper Script
자동으로 한국어 텍스트를 번역 함수로 감쌈
"""

import re
import sys

# 번역이 필요한 한국어 문자열 매핑 (한글 -> 영문)
translations = {
    # 페이지 제목
    "Azure AI Chatbot 설정": "Azure AI Chatbot Settings",
    
    # 섹션 제목
    "Azure AI 연결 설정": "Azure AI Connection Settings",
    "외관 설정": "Appearance Settings",
    
    # 필드 라벨
    "작동 모드": "Operation Mode",
    "AI 제공자": "AI Provider",
    "엔드포인트": "Endpoint",
    "배포/모델 이름": "Deployment/Model Name",
    "Agent 엔드포인트": "Agent Endpoint",
    "Agent ID": "Agent ID",
    "Client ID (App ID)": "Client ID (App ID)",
    "Client Secret": "Client Secret",
    "Tenant ID": "Tenant ID",
    "위젯 활성화": "Widget Active",
    "채팅 제목": "Chat Title",
    "환영 메시지": "Welcome Message",
    "위젯 위치": "Widget Position",
    "메인 색상": "Primary Color",
    "보조 색상": "Secondary Color",
    "미리보기": "Preview",
    
    # 옵션
    "Chat 모드 (OpenAI 호환)": "Chat Mode (OpenAI Compatible)",
    "간단한 대화형 챗봇 (API Key 인증)": "Simple conversational chatbot (API Key authentication)",
    "Agent 모드 (Azure AI Foundry)": "Agent Mode (Azure AI Foundry)",
    "고급 에이전트 기능 (Entra ID 인증 필수)": "Advanced agent features (Entra ID authentication required)",
    "기타 (OpenAI 호환)": "Other (OpenAI Compatible)",
    "오른쪽 하단": "Bottom Right",
    "왼쪽 하단": "Bottom Left",
    "오른쪽 상단": "Top Right",
    "왼쪽 상단": "Top Left",
    
    # 설명
    "사용할 AI 제공자를 선택하세요": "Select the AI provider to use",
    "API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)": "API Key is encrypted and stored securely (AES-256 encryption)",
    "Client Secret은 암호화되어 안전하게 저장됩니다. (AES-256 암호화)": "Client Secret is encrypted and stored securely (AES-256 encryption)",
    "채팅 위젯을 사이트에 표시합니다": "Display chat widget on site",
    "메인 색상과 보조 색상으로 그라데이션이 적용됩니다.": "Gradient is applied with primary and secondary colors.",
    "설정을 변경하면 실시간으로 미리보기가 업데이트됩니다.": "Preview updates in real-time when settings are changed.",
    
    # 버튼
    "설정 저장": "Save Settings",
    "연결 테스트": "Test Connection",
    "사용 가이드 보기": "View User Guide",
    
    # 기본값
    "AI 도우미": "AI Assistant",
    "안녕하세요! 무엇을 도와드릴까요?": "Hello! How can I help you?",
}

def wrap_korean_text_in_php(content):
    """PHP 파일의 한국어 텍스트를 번역 함수로 감싸기"""
    
    for korean, english in translations.items():
        # HTML 태그 내의 텍스트
        # 예: >한국어< -> ><?php esc_html_e('English', 'azure-ai-chatbot'); ?><
        pattern1 = r'>' + re.escape(korean) + r'<'
        replacement1 = r'><?php esc_html_e(\'' + english + r'\', \'azure-ai-chatbot\'); ?><'
        content = re.sub(pattern1, replacement1, content)
        
        # value 속성의 텍스트
        # 예: value="한국어" -> value="<?php esc_attr_e('English', 'azure-ai-chatbot'); ?>"
        pattern2 = r'value="' + re.escape(korean) + r'"'
        replacement2 = r'value="<?php esc_attr_e(\'' + english + r'\', \'azure-ai-chatbot\'); ?>"'
        content = re.sub(pattern2, replacement2, content)
        
        # placeholder 속성
        pattern3 = r'placeholder="' + re.escape(korean) + r'"'
        replacement3 = r'placeholder="<?php esc_attr_e(\'' + english + r'\', \'azure-ai-chatbot\'); ?>"'
        content = re.sub(pattern3, replacement3, content)
        
        # 일반 따옴표 안의 텍스트 (JavaScript 등)
        pattern4 = r"'" + re.escape(korean) + r"'"
        replacement4 = r"'<?php esc_html_e(\'" + english + r"\', \'azure-ai-chatbot\'); ?>'"
        content = re.sub(pattern4, replacement4, content)
    
    return content

def main():
    input_file = 'templates/settings-page.php'
    output_file = 'templates/settings-page.php.new'
    
    try:
        # 파일 읽기
        with open(input_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # 번역 함수로 감싸기
        wrapped_content = wrap_korean_text_in_php(content)
        
        # 파일 쓰기
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(wrapped_content)
        
        print(f"✅ 변환 완료: {output_file}")
        print(f"   원본 파일은 {input_file}.backup에 백업되어 있습니다.")
        
    except Exception as e:
        print(f"❌ 오류 발생: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main()
