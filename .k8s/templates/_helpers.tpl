{{- define "symfony-app.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{- define "symfony-app.fullname" -}}
{{- if .Values.fullnameOverride -}}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" -}}
{{- else -}}
{{- $name := default .Chart.Name .Values.nameOverride -}}
{{- if contains $name .Release.Name -}}
{{- .Release.Name | trunc 63 | trimSuffix "-" -}}
{{- else -}}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" -}}
{{- end -}}
{{- end -}}
{{- end -}}

{{- define "symfony-app.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{- define "symfony-app.labels" -}}
helm.sh/chart: {{ include "symfony-app.chart" . }}
{{ include "symfony-app.selectorLabels" . }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end -}}

{{- define "symfony-app.selectorLabels" -}}
app.kubernetes.io/name: {{ include "symfony-app.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end -}}

{{- define "symfony-app.serviceAccountName" -}}
{{- if .Values.serviceAccount.create -}}
{{- default (include "symfony-app.fullname" .) .Values.serviceAccount.name -}}
{{- else -}}
{{- default "default" .Values.serviceAccount.name -}}
{{- end -}}
{{- end -}}

{{- define "symfony-app.secretName" -}}
{{- if .Values.env.existingSecret -}}
{{- .Values.env.existingSecret -}}
{{- else -}}
{{- include "symfony-app.fullname" . -}}
{{- end -}}
{{- end -}}
